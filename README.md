# KNET-Codeigniter-Library
CI  Library to easily use KNET kit

# How to use

Include knet_lib library in your controller 
```
$this->load->library('knet_lib');
```

# Request method request()

The request method initiates the payment request to KNET gateway . 

## Parameters

| Param  | Type  | Required  | Default  | Description  |
|---|---|---|---|---|
| amount  | numeric   | yes  | -   | The amount to be charged  |
| transactionID  | string  | Yes | Yes  |  The transaction ID for records. generallly your order ID |
|  referenceID | string  |  Yes |  Yes | The reference text displayed on gateway  |
| udf1 | string | No | - | User Defined field |
| udf2 | string | No | - | User Defined field |
| udf3 | string | No | - | User Defined field |
| paymentType | numeric | No | 1 | Payment Mode  |
| lang | string | No | en | Language for payment gateway. 'en' or 'ar' |


# response Method response()

the response method recieves the callback data from gateway and returns the unencrypted array.

