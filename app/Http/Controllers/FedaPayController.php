<?php

namespace App\Http\Controllers;

use http\Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class FedaPayController extends Controller
{

    /**
     * @OA\Post(
     *     path="fedapay/api/transaction",
     *     summary="Créer une transaction",
     *     description="Crée une transaction avec les données reçues du formulaire",
     *     operationId="createTransaction",
     *     tags={"FedaPay"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Données de la transaction",
     *         @OA\JsonContent(
     *             @OA\Property(property="description", type="string", example="Transaction for john.doe@example.com"),
     *             @OA\Property(property="amount", type="integer", example=2000),
     *             @OA\Property(property="currency", type="object", required={"iso"}, @OA\Property(property="iso", type="string", example="XOF")),
     *             @OA\Property(property="callback_url", type="string", example="https://www.monsite.com/callback"),
     *             @OA\Property(property="customer", type="object", required={"email"}, @OA\Property(property="email", type="string", example="john.doe@example.com"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirection vers le lien de paiement",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */

    public function createTransaction(Request $request)
    {

        $name = $request->name;
        $numero = $request->numero;
        $montant = $request->montant;
        $email = $request->email;

        $apiKey = config('paiement.apiKey');
        /* Remplacez VOTRE_CLE_API par votre véritable clé API */
        \FedaPay\FedaPay::setApiKey($apiKey);

        /* Précisez si vous souhaitez exécuter votre requête en mode test ou live */
        \FedaPay\FedaPay::setEnvironment('sandbox'); //ou setEnvironment('live');

        $transaction =   \FedaPay\Transaction::create(array(
            "description" => "Transaction for john.doe@example.com",
            "amount" => 1,
            "currency" => ["iso" => "XOF"],
            //"callback_url" => "https://maplateforme.com/callback",
            "customer" => [
                "firstname" => "John",
                "lastname" => "Doe",
                "email" => "john.doe@example.com",
                "phone_number" => [
                    "number" => "+22952266197",
                    "country" => "bj"
                ]
            ]
        ));

        var_dump($transaction);

        // $transaction = \FedaPay\Transaction::create(array(
        //     "description" => "Transaction for john.doe@example.com",
        //     "amount" => 1,
        //     "currency" => ["iso" => "XOF"],
        //     "callback_url" => "http://localhost:8000",
        //     "customer" => [
        //         "firstname" => "Emma",
        //        // "lastname" => "Doe",
        //         "email" => "jahounoel@gmail.com",
        //         "phone_number" => [
        //       "number" => "+22952266197",
        //          //   "number" => "+229" . $numero,
        //             "country" => "bj"
        //         ]
        //     ]
        // ));


        // Générez le token pour la transaction
        $token = $transaction->generateToken();
        echo 'hello' . $token;

        // Redirigez l'utilisateur vers le lien de paiement
        //  return redirect($token->url);
     //   return header('lienpaiement: ' . $token->url);

        // $callbackStatus = $this->handleCallback($request);

        // if ($callbackStatus === 'approved') {
        //     // Le paiement a été approuvé, mettre à jour le portefeuille de l'utilisateur

        //     // Rediriger vers la page de succès ou afficher un message de succès
        //     // return redirect()->route('achat.success')->with('success', 'Achat de Kiss effectué avec succès!');
        //     return response()->json(['message' => 'Achat de Kiss effectué avec succès!'], 200);
        // }
        //  return Redirect::away($token->url);
        // } catch (\FedaPay\Error\ApiConnection $e) {
        //     // Gérez l'erreur de connexion à l'API
        // } catch (\FedaPay\Error\InvalidRequest $e) {
        //     // Gérez l'erreur de requête invalide
        // }
    }

     /**
     * @OA\Get(
     *     path="/fedapay/api/callback",
     *     summary="Gérer le retour d'appel de transaction",
     *     description="Traite le retour d'appel de la transaction et effectue les actions appropriées",
     *     operationId="handleCallback",
     *     tags={"FedaPay"},
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="ID de la transaction",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Statut de la transaction",
     *         required=true,
     *         @OA\Schema(type="string", enum={"approved", "canceled"})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Paiement effectué")
     *         )
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirection vers une page appropriée"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur interne du serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erreur interne du serveur")
     *         )
     *     )
     * )
     *
     * @param Request $request
     * @return RedirectResponse|JsonResponse
     */
    public function handleCallback(Request $request)
    {
        // Récupérez les paramètres d'ID et de statut de la transaction depuis l'URL
        $transactionId = $request->query('id');
        $status = $request->query('status');

        // Adressez une nouvelle requête à l'API pour obtenir le véritable statut de la transaction
        try {
            // Configurez votre clé API et l'environnement
            $apiKey = config('paiement.apiKey');
            /* Remplacez VOTRE_CLE_API par votre véritable clé API */
            \FedaPay\FedaPay::setApiKey($apiKey);

            /* Précisez si vous souhaitez exécuter votre requête en mode test ou live */
            \FedaPay\FedaPay::setEnvironment('sandbox'); //ou setEnvironment('live');



            if ($transactionId && $status) {
                $transaction = \FedaPay\Transaction::retrieve($transactionId);


                // Vérifiez si le statut de la transaction correspond au statut renvoyé dans l'URL
                if ($transaction->status === $status) {
                    // Le statut correspond, effectuez les traitements appropriés
                    if ($status === 'approved') {
                        // La transaction a été approuvée
                        // Effectuez les actions appropriées
                        return response()->json([
                            'message' => 'Paiement effectué',
                            'status' => $status,
                        ], 200);
                    } elseif ($status === 'canceled') {
                        // La transaction a été annulée
                        // Effectuez les actions appropriées

                        // Redirigez l'utilisateur vers une page appropriée
                        return redirect()->route('transaction.status', ['status' => $status]);
                    } elseif ($status == "pending") {
                        return redirect()->route('transaction.status', ['status' => $status]);
                    } elseif ($status == "declined") {
                        return redirect()->route('transaction.status', ['status' => $status]);
                    }
                } else {
                    // Le statut ne correspond pas, gérer l'incohérence des données
                    return redirect()->route('error')->with('message', 'Une erreur s\'est produite lors du traitement de la transaction. Veuillez réessayer plus tard.');
                }
            }
        } catch (Exception $e) {
            // Gérez les erreurs
            return response()->json([
                'message' => 'Erreur interne du serveur',
                'status' => 'error',
            ], 500);
        }
    }


    /**
     *
     * @OA\Get(
     *     path="/api/transaction/status/{status}",
     *     summary="Transaction Status",
     *     description="Displays the transaction status page based on the returned status",
     *     operationId="getTransactionStatus",
     *     tags={"FedaPay"},
     *     @OA\Parameter(
     *         name="status",
     *         in="path",
     *         description="Transaction status",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="approved")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Transaction not found")
     *         )
     *     )
     * )
     * @param $status
     * @return Application|Factory|View
     */

    public function transactionStatus($status)
    {
        // Affichez la page de statut de la transaction en fonction du statut renvoyé
        return view('transaction.status', ['status' => $status]);
    }


}
