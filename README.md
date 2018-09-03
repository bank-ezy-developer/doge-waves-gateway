# doge-waves-gateway
Dogecoin waves bridge gateway

A dogecoin to waves bridge gateway that will enable a 1:1 exchange between dogecoin and doge on the waves decentralized exchange

Dogecoin can be exchanged directly to the waves blockchain through a bridge for a token that is created on the waves blockchain. The gateway will enable a 1:1 exchange of dogecoin to the dogecoin token on the waves decentralized exchange. 

The dogecoin token on the waves blockchain can be directly exchanged for dogecoin on the doge blockchain. The gateway will enable a 1:1 exchange of the dogecoin token to the dogecoin blockchain. 

This is achieved with loading 2 wallets with dogecoin and dogecoin tokens respectively that are used to enable the exchange between the tokens and coins. 

When a dogecoin coin to token exchange is requested a new dogecoin address will be created where the user will have to send the dogecoins he wishes to exchange for dogecoin tokens. Once the dogecoin is received and confirmed in the wallet tokens will be sent to the users nominated token wallet. 

Similarly when a dogecoin token to dogecoin coin exchange is requested a new waves address will be created where the user will have to send his dogecoin tokens that he wishes to exchange for dogecoin coins. Once the tokens have been received and confirmed in the wallet the coins will be sent to the users nominated coin wallet

An API is created that enables the creation of the waves wallet and exchanging the waves tokens. This API is only available on the server that will run the gateway. This is done using a python script. 

For the dogecoin coin wallet will be run using a json rpc interface wallet service using the dogecoin core. This is implemented with php code. 

All transactions are logged into a mysql database.

Requirements to run the gateway: 
Ubuntu LAMP server with the dogecoin core installed. 
Python

Doge Asset ID on the Waves DEX: DvpmDF45bmo9xDb6rSte8uPPWWeh6EGzoATwxMStWQKT
