# Intégration de FedaPay dans un projet Laravel

## Introduction

FedaPay est une solution de paiement en ligne qui permet aux entreprises de recevoir des paiements via des portefeuilles électroniques, cartes bancaires, et autres méthodes de paiement disponibles dans plusieurs pays africains. Dans ce guide, nous allons vous montrer comment intégrer FedaPay dans votre application Laravel.

### Prérequis

-   Avoir un projet Laravel installé.
-   Avoir un compte FedaPay (sandbox ou production).
-   Avoir une clé publique FedaPay pour utiliser les API.
-   Avoir une clé privé FedaPay pour utiliser les API.

## Étapes d'intégration

### 1. Installation du package FedaPay

Vous devez d'abord installer le package officiel de FedaPay pour PHP. Utilisez Composer pour l'installer dans votre projet Laravel :

```bash
composer  require  fedapay/fedapay-php
```

### 2. Ajouter le script js de fedapay dans votre vu : index.blade.php

```bash
<script  src="https://cdn.fedapay.com/checkout.js?v=1.1.7"></script>
```

### 3 . Exemple de scripte js

```bash
<script  type="text/javascript">
function  iniPaiement(id, montant, titre) {
// Affichage dans la console
console.log('ID:', id);
console.log('Montant:', montant);
console.log('Titre:', titre);
let  lien = "/";
FedaPay.init('#pay-btn', {
public_key:  'votre clé public fedapay',
transaction: {
amount:  montant,
description:  'Acheter mon produit',
custom_metadata: {
id:  id,
montant:  montant,
titre:  titre
}
},
customer: {
email:  '',
lastname:  '',
firstname:  ''
},
// Ajout d'un callback onComplete pour rediriger manuellement
onComplete:  function(transaction) {
// console.log('transaction:', transaction);
window.location.href = lien;
}
});
}
</script>
```

#### NB:

les données du custom_metadata sont obtenues dynamiquements, veuillez implémenter le mini projet
et mettre des informations dans votre base de donnée

### 3 . Exemple de Route

```bash
Route::post('/paiement/Webhook', [ApkController::class, 'paiementWebhook'])->name('paiementWebhook');
```

### 4 . configuration de crsf token

Dans le **Middleware** **VerifyCsrfToken** ajouter ce code :

```bash
protected  $except = [
'paiement/Webhook'
];
```

### 5 . Exemple de code php pour votre webHook au niveau du controller

```bash
public  function  paiementWebhook(Request  $request){
$endpoint_secret = 'la cle secrete de votre webhook';
$webhookData = $request->all();
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_X_FEDAPAY_SIGNATURE'];
$event = null;
if (!isset($webhookData['event']) ) {
return  response()->json([
'message' => ' webhook invalides ',
], 400);
}
try {

$event = \FedaPay\Webhook::constructEvent(
$payload,
$sig_header,
$endpoint_secret
);
} catch (\UnexpectedValueException  $e) {
// Invalid payload
http_response_code(400);
exit();
} catch (\FedaPay\Error\SignatureVerification  $e) {
// Invalid signature
http_response_code(400);
exit();
}
// Handle the event
switch ($event->name) {
case  'transaction.created':
http_response_code(202);
exit();
break;
case  'transaction.approved':
// recuperer des information de paiement de cette facon; valable les elements du custom_data
$montant = $webhookData['entity']['custom_metadata']['montant'];
// avec ces switchs case vous pouvez faire toute les verifications que vous voulez avant enregistrement en bas de données
switch (true) {
case (1):
return  response()->json([
'message' => 'Montant inférieur à la valeur minimale',

], 200);
exit();
break;
case (2):
return  response()->json([
'message' => 'Montant valide et dans la plage autorisée',
], 200);
exit();
break;
default:
return  response()->json([
'message' => 'autre paiemenent',
], 400);
}
exit();
break;
case  'transaction.canceled':
http_response_code(203);
exit();
break;
default:
return  response()->json([
'message' => 'feda ne gere pas ',
], 400);
exit();
}
}
```

<hr> 
Implémenté et tester.
en cas de difficulté je suis joignable par WhatsApp au   **+229 62099124** 
<hr> 
 
> **Note:**  **le webHook de fedapys est fonctionnel une fois sur server**  vous pouvez tout mettre en place en local et faire vos configuration une fois sur un serveur ou en production
