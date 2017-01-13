## PHP BankID Base

This is a base client for integrating against the BankID solution. It handles all the connections and verifications against the BankID Central Server. You can use it to create a custom implementation for your project.

## Code Example

First start off a request like this:
```
$bankid_client = new BankID( 'certificate.name.pem' );
...
$order_reference = $bankid_client->authenticate( 'yyyymmddxxxx' );
```
This will return an order reference you can use to collect the status of the request from BankIDs central servers.
This is something you can do at most every 2 seconds.
```
$bankid_client->collect( 'order_ref_given' );
```

## Contributors

Alexander Karlsson <alexander@livetime.nu>

Karl Berggren info[a]jjabba.com
