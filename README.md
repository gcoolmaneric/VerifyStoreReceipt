# VerifyStoreReceipt
 This is a simple PHP to validate Receipt for Apple and Google. 

# How to integrate it with Unity Prime31 Plugin ?

  Import Prime31 plugins
    StoreKit for Apple Store and In-App-Billing for Google Play into Unity.
  
# How to setup In-App-Purchase for IOS ? 

There are two steps for this.
  
  - Setup Item`s Product Id
  - Setup Payment Success Event to Get Receipt 

## Setup Item`s Product Id

  Open a demo scene in folder 
  ```
  /Plugins/Prime31/Storekit/demo/StoreKitTestScene.
  ```
 
 Edit StoreKitGUIManager.cs and modify Product Id.
 ```
 /Plugins/Prime31/Storekit/demo/ StoreKitGUIManager.cs

 // XXXX is the product Id, which must match what you have in iTunes.
 var productIdentifiers = new string[] { "XXXX" };
 StoreKitBinding.requestProductData( productIdentifiers );
 ```
## Setup Payment Success Event to Get Receipt

The following function aims to capture the receipt from Apple when the payment becomes successful, 

and send the receipt to PHP to validate the receipt`s correctness.

```
Edit /Plugins/Prime31/Storekit/demo/ StoreKitEventListener.cs

void purchaseSuccessfulEvent( StoreKitTransaction transaction )
   {
       Debug.Log( "purchaseSuccessfulEvent: " + transaction );

       // Get iOS receipt 
       string receipt = transaction.base64EncodedTransactionReceipt;

       // Build POST form
       WWWForm form = new WWWForm ();
       form.AddField ("key", "1234");
       form.AddField ("receipt", receipt);
       form.AddField ("en", "prod") // dev, prod
       form.AddField ("os", "ios")  // ios, android
       
       // Server URL
       string url = "http://your server IP/verifyPayment.php";

       // Process respond
       StartCoroutine(this.DoWWW(new WWW(url, form), (www) => {
           Debug.Log("-------- Callback Success: " + www.text);
       }));
   }

```
 
# How to setup In-App-Billing for Android ? 

There are two steps for this.
  
  - Setup App Public Key
  - Setup Item`s Product Id
  - Setup Payment Success Event to Get Receipt 

## Setup App Public Key

Open a demo scene in folder 
```
/Plugins/ InAppBillingAndroid /demo/IABTestScene.unity
```

Setup Public key 

```
Edit /Plugins/ InAppBillingAndroid /demo/ IABUIManager.cs

// Setup Public key 
var key = "Your Public Key on Google Play";

GoogleIAB.init( key );
```
 
# Setup Item`s Product Id
```
Edit /Plugins/ InAppBillingAndroid /demo/ IABUIManager.cs

// Setup Product ID 
private string[] skus = new string[] 
{
	"XXXXXX"  //  your Product Id here 
};

GoogleIAB.queryInventory( skus );
```

# Setup Payment Success Event to Get Receipt 

```
Edit /Plugins/ InAppBillingAndroid /demo/ GoogleIABEventListener.cs


void purchaseCompleteAwaitingVerificationEvent( string purchaseData, string signature )
{
	Debug.Log( "purchaseCompleteAwaitingVerificationEvent. purchaseData: " + purchaseData + ", signature: " + signature );
	Prime31.Utils.logObject (purchaseData);
	Prime31.Utils.logObject (signature);

	// Goole receipt 
	string receipt = purchaseData;

	// Initil POST form
	WWWForm form = new WWWForm ();
	form.AddField ("key", "1234");
	form.AddField ("os", "android");
	form.AddField ("en", "prod");
	form.AddField ("receipt", receipt);
 form.AddField ( "sing", signature);

	// Server URL
	string url = "http://your server ip/veryPayment.php";
	// Process respond
	StartCoroutine (this.DoWWW (new WWW (url, form), (www) => 
	{
		Debug.Log("-------- Callback Success: " + www.text);
	}));
}
```


