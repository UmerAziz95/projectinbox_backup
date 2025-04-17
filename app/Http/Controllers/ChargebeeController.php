<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ChargeBee\ChargeBee\Models\HostedPage;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller; // ✅ Add this line
use ChargeBee\ChargeBee\Models\Item;
use ChargeBee\ChargeBee\Models\ItemPrice;
use ChargeBee\ChargeBee\Models\Subscription;
use ChargeBee\ChargeBee\Models\Refund;
use ChargeBee\ChargeBee\Models\Invoice;

class ChargebeeController extends Controller
{
    // public function subscribe(Request $request)
    // {
    //     try {
    //         // Example: passed item_id from form (not item_price_id)
    //         $requestedItemId = $request->plan_id; // e.g., 'cbdemo_business-suite'
    //         $itemPriceId = null;

    //         // Fetch all item prices
    //         $allPrices = ItemPrice::all(['limit' => 100]);
    //         $plan = null;
    //         // Search for the item_price matching the item_id
    //         foreach ($allPrices as $entry) {
    //             $itemPrice = $entry->itemPrice();
    //             if ($itemPrice->itemId === $requestedItemId) {
    //                 // dd($requestedItemId, $itemPrice);
    //                 $itemPriceId = $itemPrice->id;
    //                 $plan = [
    //                     'id' => $itemPrice->id,
    //                     'name' => $itemPrice->name,
    //                     'item_family_id' => $itemPrice->itemFamilyId,
    //                     'item_id' => $itemPrice->itemId,
    //                     'description' => $itemPrice->description,
    //                     'status' => $itemPrice->status,
    //                     'external_name' => $itemPrice->externalName,
    //                     'pricing_model' => $itemPrice->pricingModel,
    //                     'price' => $itemPrice->price,
    //                     'period' => $itemPrice->period,
    //                     'currency_code' => $itemPrice->currencyCode,
    //                     'period_unit' => $itemPrice->periodUnit,
    //                     'free_quantity' => $itemPrice->freeQuantity,
    //                     'channel' => $itemPrice->channel,
    //                     'resource_version' => $itemPrice->resourceVersion,
    //                     'updated_at' => $itemPrice->updatedAt,
    //                     'created_at' => $itemPrice->createdAt,
    //                     'is_taxable' => $itemPrice->isTaxable,
    //                     'item_type' => $itemPrice->itemType,
    //                     'show_description_in_invoices' => $itemPrice->showDescriptionInInvoices,
    //                     'show_description_in_quotes' => $itemPrice->showDescriptionInQuotes,
    //                     'deleted' => $itemPrice->deleted,
    //                 ];
    //                 break;
    //             }
    //         }

    //         if (!$itemPriceId) {
    //             return response()->json([
    //                 'success' => false,
    //                 'message' => "Item Price for '{$requestedItemId}' not found."
    //             ]);
    //         }

    //         // Proceed with subscription using the found item_price_id
    //         $result = HostedPage::checkoutNewForItems([
    //             "subscription_items" => [
    //                 [
    //                     "item_price_id" => $itemPriceId,
    //                     "quantity" => 1
    //                 ]
    //             ],
    //             "customer" => [
    //                 "first_name" => $request->first_name,
    //                 "email" => $request->email,
    //             ],
    //             "redirect_url" => route('subscription.success'),
    //             // "cancel_url" => route('subscription.success') // optional
    //             "cancel_url" => url('/subscription.success') // optional
    //         ]);

    //         return response()->json([
    //             'plan' => $plan,
    //             'success' => true,
    //             'url' => $result->hostedPage()->url
    //         ]);

    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Subscription failed: ' . $e->getMessage()
    //         ]);
    //     }
    // }

    public function subscribe(Request $request)
    {
        try {
            $itemPriceId = $request->plan_id; // This should be the actual item_price_id
    
            // Try to retrieve the item_price directly
            $itemPrice = \ChargeBee\ChargeBee\Models\ItemPrice::retrieve($itemPriceId)->itemPrice();
    
            if ($itemPrice->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => "Item price is not active."
                ]);
            }
    
            // Proceed with hosted page creation
            $result = \ChargeBee\ChargeBee\Models\HostedPage::checkoutNewForItems([
                "subscription_items" => [
                    [
                        "item_price_id" => $itemPriceId,
                        "quantity" => 1
                    ]
                ],
                "customer" => [
                    "first_name" => $request->first_name,
                    "email" => $request->email,
                ],
                "redirect_url" => route('subscription.success'),
                "cancel_url" => route('subscription.cancel')
            ]);
    
            return response()->json([
                'success' => true,
                'url' => $result->hostedPage()->url
            ]);
    
        } catch (\ChargeBee\ChargeBee\APIError $e) {
            return response()->json([
                'success' => false,
                'message' => 'Chargebee error: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription failed: ' . $e->getMessage()
            ]);
        }
    }
      

     // Cancel Subscription Method
     public function cancelSubscription(Request $request)
     {
         $subscriptionId = $request->get('subscription_id'); // Get subscription ID from request
 
         try {
             // Cancel subscription
             $result = Subscription::cancel($subscriptionId, [
                 'cancel_at_end_of_period' => true, // Cancel immediately or at the end of the billing period
             ]);
 
             // Check if the subscription was successfully canceled
             $subscription = $result->subscription();
             if ($subscription->status === 'cancelled') {
                 return response()->json([
                     'success' => true,
                     'message' => 'Subscription has been canceled successfully.',
                 ]);
             }
 
             return response()->json([
                 'success' => false,
                 'message' => 'Failed to cancel the subscription.',
             ]);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'Error canceling subscription: ' . $e->getMessage(),
             ]);
         }
     }
 
     // Refund Payment Method
     public function refundPayment(Request $request)
     {
         $invoiceId = $request->get('invoice_id'); // Get invoice ID from request
 
         try {
             // Fetch the invoice to check if it's refundable
             $invoiceResult = Invoice::retrieve($invoiceId);
             $invoice = $invoiceResult->invoice();
 
             // Check if the invoice can be refunded (e.g., it's not already refunded)
             if ($invoice->status !== 'paid') {
                 return response()->json([
                     'success' => false,
                     'message' => 'Invoice is not in paid status, refund cannot be processed.',
                 ]);
             }
 
             // Refund the payment (you can specify the amount to refund)
             $refundAmount = $invoice->amount_paid; // Full refund
             $result = Refund::create([
                 'invoice_id' => $invoiceId,
                 'amount' => $refundAmount, // Full refund or specify partial amount
                 'gateway' => $invoice->gateway,
                 'payment_source_id' => $invoice->payment_source_id,
                 'refund_reason' => 'Requested by customer', // Customize the reason
             ]);
 
             // Check if refund was successful
             $refund = $result->refund();
             if ($refund->status === 'successful') {
                 return response()->json([
                     'success' => true,
                     'message' => 'Refund has been processed successfully.',
                 ]);
             }
 
             return response()->json([
                 'success' => false,
                 'message' => 'Failed to process the refund.',
             ]);
         } catch (\Exception $e) {
             return response()->json([
                 'success' => false,
                 'message' => 'Error processing refund: ' . $e->getMessage(),
             ]);
         }
     }
 
     // Subscription Success Method (You can customize this as per your logic)
     public function subscriptionSuccess(Request $request)
{
    try {
        $hostedPageId = $request->input('hostedpage_id');

        if (!$hostedPageId) {
            return response()->json([
                'success' => false,
                'message' => 'Missing hostedpage_id in request.'
            ]);
        }

        // Retrieve the hosted page details
        $result = HostedPage::retrieve($hostedPageId);
        $hostedPage = $result->hostedPage();

        // Get subscription ID from hosted page result
        $subscriptionId = $hostedPage->content()->subscription->id;
        $customerId = $hostedPage->content()->customer->id;

        return response()->json([
            'success' => true,
            'message' => 'Subscription successful.',
            'subscription_id' => $subscriptionId,
            'customer_id' => $customerId
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Failed to confirm subscription: ' . $e->getMessage()
        ]);
    }
}
 
     // Subscription Cancel Method
     public function subscriptionCancel(Request $request)
     {
         return redirect('/plans')->with('error', 'Subscription process was cancelled.');
     }
}


