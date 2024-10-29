<?php

namespace App\Http\Controllers;

use FedaPay\FedaPay;
use FedaPay\Webhook;
use App\Models\Paiement;
use FedaPay\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use FedaPay\Error\SignatureVerification;

class ApkController extends Controller
{
    public function paiementWebhook(Request $request)
    {

        $endpoint_secret = 'la cle secrete de votre webhook';
        $webhookData = $request->all();
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_X_FEDAPAY_SIGNATURE'];
        $event = null;


        if (!isset($webhookData['event']) ) {
            return response()->json([
                'message' => ' webhook invalides ',
            ], 400);
        }


        try {
            $event = \FedaPay\Webhook::constructEvent(
                $payload,
                $sig_header,
                $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch (\FedaPay\Error\SignatureVerification $e) {
            // Invalid signature

            http_response_code(400);
            exit();
        }

        // Handle the event
        switch ($event->name) {
            case 'transaction.created':

                http_response_code(202);
                exit();
                break;
            case 'transaction.approved':

                // recuperer des information de paiement de cette facon 
                $montant = $webhookData['entity']['custom_metadata']['montant'];
 
                // avec ces switchs case vous pouvez faire toute les verifications que vous voulez

                switch (true) {
                    case (1):

                        return response()->json([
                            'message' => 'Montant inférieur à la valeur minimale',
                        ], 200);
                        exit();
                        break;

                    case (1):

                        return response()->json([
                            'message' => 'Montant valide et dans la plage autorisée',
                        ], 200);
                        exit();
                        break;

                    case (1):

                        return response()->json([
                            'message' => 'Montant supérieur à la valeur maximale',
                        ], 200);
                        exit();
                        break;

                    default:

                        return response()->json([
                            'message' => 'autre paiemenent',
                        ], 400);
                }
                exit();
                break;

            case 'transaction.canceled':

                http_response_code(203);
                exit();
                break;
            default:
                return response()->json([
                    'message' => 'feda ne gere pas ',
                ], 400);
                exit();
        }

    }
}
