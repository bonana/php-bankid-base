## PHP BankID Base

This is a base client for integrating against the BankID solution. It handles all the connections and verifications against the BankID Central Server. You can use it to create a custom implementation for your project.

## Code Example

```
$bankid_client = new BankID( 'certificate.name.pem' );
...
$bankid_client->authenticate( 'yyyymmddxxxx' );
```

## Contributors

Alexander Karlsson <alexander@livetime.nu>