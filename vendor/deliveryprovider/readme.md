Postmates Api Uses
Using the Postmates Delivery API, developers can integrate our 
on-demand local delivery platform into their applications. 
The API is designed to allow application developers to check prices, 
schedules, book a delivery, then follow updates on that delivery till completion.
The [Postmates Platform](https://postmates.com/developer/docs) is
a set of APIs that make your app more realable delivery service.

This repository contains the open source that allows you to
access Postmates Platform from your PHP app.

```
First Register with postmates
Get CUSTOMER ID
Get API Key
```

Usage
-----

The [examples][examples] are a good place to start. The minimal you'll need to
have is:
```php
```
Require 'curl configured';


```
To make [API][API] calls:
```

```
status should be defined in your application configuration file to true or false. 
true : you want to use this service, 
False : You don't want to use this service
```
if (STATUS) { 
``` Create object of postmates to pass required parameter to __construct
POSTMATE = new Postmates(array(
  'customeid'  => 'YOUR_CUSTOMER_ID',
  'apikey' => 'YOUR_APP_KEY',
   'apiurl'=>'API_URL'
));
```
 Proceed to get delivery quote.
```
    RESULT WILL JSON FORMATE DATA ABOUT YOUR DELIVERY

    A Delivery Quote provides an estimate for a potential delivery. 
    This includes the amount the delivery is expected to cost as well 
    as an estimated delivery window. As demand on our platform changes 
    over time, the amount and eta may increase beyond what your use-case can support.
 
```
Delivery Quote  
 RESULT = $postmates->getDeliveryQuote(array(
    "dropoff_address"=>"20 McAllister St, San Francisco, CA 94102",
    "pickup_address"=>"101 Market St, San Francisco, CA 94105"));

    Result will in following format
     {
      kind: "delivery_quote",
      id: "dqt_qUdje83jhdk",
      created: "2014-08-26T10:04:03Z",
      expires: "2014-08-26T10:09:03Z",
      fee: 799,
      currency: "usd",
      dropoff_eta: "2014-08-26T12:15:03Z",
      duration: 60
    }

   
```

```
Delivery 

RESULT = $postmates->getDeliveryPrice(array("manifest"=>"1 Stuffed Puppy",
 "pickup_name"=>"Puppies On-Demand",//it may be in array
 "pickup_address"=>"101 Market St, San Francisco, CA 94105",
 "pickup_phone_number"=>"555-555-5555",
 "pickup_notes"=>"Just come inside, give us order #123",
 "dropoff_name"=>"Alice Customer",
 "dropoff_address"=>"20 McAllister St, San Francisco, CA 94102",
 "dropoff_phone_number"=>"415-555-5555",
 "quote_id"=>"dqt_K7SCxZJzteH9R-"));
Result will in following format
{
    "id": "del_K7SD1dUd5aqLU-"
    "kind": "delivery",
    "live_mode": false,
    "status": "pending",
    "complete": false,
    "updated": "2014-12-09T19:31:22Z",
    "fee": 799,
    "currency": "usd", 
    "quote_id": "dqt_K7SCxZJzteH9R-",
    "courier": null,
    "created": "2014-12-09T19:31:22Z",
    "manifest": {
        "description": "1 Stuffed Puppy"
    },
    "pickup": {
        "phone_number": "555-555-5555",
        "notes": "Just come inside, give us order #123",
        "location": {
            "lat": 37.7930812,
            "lng": -122.395944
        },
        "name": "Puppies On-Demand",
        "address": "101 Market Street"
    },
    "dropoff": {
        "phone_number": "415-555-5555",
        "notes": "",
        "location": {
            "lat": 37.7811372,
            "lng": -122.4123037
        },
        "name": "Alice Customer",
        "address": "20 McAllister Street"
    },
    "dropoff_deadline": "2014-12-09T20:31:22Z",
    "pickup_eta": null,
    "dropoff_eta": null,
 }

```
```
Delivery status
Result = $postmates->getDiliveryStatus(DELIVERY_ID);

```
Tests (Command line)
-----
```
DELIVERY QUOTE
curl -u YOUR_API_KEY: \
 -d "dropoff_address=20 McAllister St, San Francisco, CA 94102" \
 -d "pickup_address=101 Market St, San Francisco, CA 94105" \
 -X POST https://api.postmates.com/v1/customers/YOU_CUSTOMER_ID/delivery_quotes
```

DELIVERY
curl -u YOUR_API_KEY: \
 -d "manifest=1 Stuffed Puppy" \
 -d "pickup_name=Puppies On-Demand" \
 -d "pickup_address=101 Market St, San Francisco, CA 94105" \
 -d "pickup_phone_number=555-555-5555" \
 -d "pickup_notes=Just come inside, give us order #123" \
 -d "dropoff_name=Alice Customer" \
 -d "dropoff_address=20 McAllister St, San Francisco, CA 94102" \
 -d "dropoff_phone_number=415-555-5555" \
 -d "quote_id=dqt_K7SCxZJzteH9R-" \
 -X POST https://api.postmates.com/v1/customers/YOUR_CUSTOMER_ID/deliveries

DELIVERY STATUS
curl -u YOUR_API_KEY: \
https://api.postmates.com/v1/customers/YOUR_CUSTOMER_ID/deliveries/DELIVERY_ID

