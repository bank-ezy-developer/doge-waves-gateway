#!/usr/bin/env python

'''
Functional REST server for Python (2.7) that enables the exchange of Dogetokens 
created on the waves blockchain and dogecoins on the dogecoin blockchain 
* Map URI patterns using regular expressions
* Map any/all the HTTP VERBS (GET, PUT, DELETE, POST)
* All responses and payloads are converted to/from JSON for you
* Easily serve static files: a URI can be mapped to a file, in which case just GET is supported
* You decide the media type (text/html, application/json, etc.)
* Correct HTTP response codes and basic error messages
* Simple REST client included! use the rest_call_json() method

The API enables the creation of new Waves wallets as well as transferring assets between waves wallets
Create the file web/index.html if you'd like to test serving static files. It will be served from the root URI.
@author: Bank-ezy developer (bank-ezy @ github.com)
'''

import sys, os, re, shutil, json, urllib, urllib2, BaseHTTPServer, pywaves as pw

# Fix issues with decoding HTTP responses
reload(sys)
sys.setdefaultencoding('utf8')

here = os.path.dirname(os.path.realpath(__file__))

accounts = {}


def get_accounts(handler):
    return accounts

def get_waves_balance_address(handler):
    address = urllib.unquote(handler.path[24:])
    try:
        Account = pw.Address(address)
        balance = Account.balance(confirmations = 20)
        return balance
    except:
        return "error"

def get_waves_balance(handler):
    Account1 = pw.Address(seed='Seed of your token wallet that will be funded with tokens for the bridge')
    balance = Account1.balance(confirmations = 20)
    return balance

def get_newwavesaddress(handler):
    newaccount = pw.Address()
    newwavesaddress = newaccount.address
    return newwavesaddress

def get_newaccount(handler):
    newaccount = pw.Address()
    newaccountaddress = newaccount.address
    newaccountprivatekey = newaccount.privateKey
    accountdata = {}
    accountdata['address'] = newaccountaddress
    accountdata['privatekey'] = newaccountprivatekey
    return accountdata

def set_newaccount(handler):
    key = urllib.unquote(handler.path[8:])
    payload = handler.get_payload()
    accounts[key] = payload
    return accounts[key]

def get_dogetoken_balance(handler):
    Account1 = pw.Address(seed='Seed of your token wallet that will be funded with tokens for the bridge')
    balance = Account1.balance('DvpmDF45bmo9xDb6rSte8uPPWWeh6EGzoATwxMStWQKT')
    return balance

def send_001_waves(handler):
    key = urllib.unquote(handler.path[14:])
    #key = "'" + key + "'"
    Account1 = pw.Address(seed='Seed of your token wallet that will be funded with tokens for the bridge')
    Account1.sendWaves(recipient = pw.Address(key), amount = 100000)
    return key

def send_doge_back(handler):
    key = urllib.unquote(handler.path[13:])
    Account1 = pw.Address(privateKey=key)
    amounttosend = Account1.balance('DvpmDF45bmo9xDb6rSte8uPPWWeh6EGzoATwxMStWQKT')
    myToken = pw.Asset('DvpmDF45bmo9xDb6rSte8uPPWWeh6EGzoATwxMStWQKT')
    Account1.sendAsset(recipient = pw.Address('WavesWalletAddressUsedForExchange'), asset = myToken, amount = amounttosend)
    return amounttosend

def send_doge_user(handler):
    key = urllib.unquote(handler.path[13:])
    Account1 = pw.Address(seed='Seed of your token wallet that will be funded with tokens for the bridge')
    dogeToken = pw.Asset('DvpmDF45bmo9xDb6rSte8uPPWWeh6EGzoATwxMStWQKT')
    jsondata = accounts[key]
    recipientaddress = jsondata['address']
    intamounttosend = round(float(jsondata['amount']), 0)
    amounttosendtouser = int(intamounttosend)
    Account1.sendAsset(recipient = pw.Address(recipientaddress), asset = dogeToken, amount = amounttosendtouser)
    return amounttosendtouser

def get_account(handler):
    key = urllib.unquote(handler.path[9:])
    return accounts[key] if key in accounts else None

def get_account_balance(handler):
    key = urllib.unquote(handler.path[16:])
    account_to_get_balance = accounts[key] if key in accounts else None
    #data = json.loads(str(account_to_get_balance))
    balance = pw.Address(account_to_get_balance["address"]).balance()
    return balance

def get_account_balance_doge(handler):
    key = urllib.unquote(handler.path[19:])
    account_to_get_balance = accounts[key] if key in accounts else None
    #data = json.loads(str(account_to_get_balance))
    balance = pw.Address(account_to_get_balance["address"]).balance('DvpmDF45bmo9xDb6rSte8uPPWWeh6EGzoATwxMStWQKT')
    return balance
    

def set_account(handler):
    key = urllib.unquote(handler.path[9:])
    payload = handler.get_payload()
    accounts[key] = payload
    return accounts[key]

def send_doge(handler):
    key = urllib.unquote(handler.path[8:])
    payload = handler.get_payload()
    receiveraddress = payload
    print(receiveraddress)
    return receiveraddress

def delete_account(handler):
    key = urllib.unquote(handler.path[8:])
    del accounts[key]
    return True # anything except None shows success

def delete_newaccount(handler):
    key = urllib.unquote(handler.path[8:])
    del accounts[key]
    return True # anything except None shows success

def rest_call_json(url, payload=None, with_payload_method='PUT'):
    'REST call with JSON decoding of the response and JSON payloads'
    if payload:
        if not isinstance(payload, basestring):
            payload = json.dumps(payload)
        # PUT or POST
        response = urllib2.urlopen(MethodRequest(url, payload, {'Content-Type': 'application/json'}, method=with_payload_method))
    else:
        # GET
        response = urllib2.urlopen(url)
    response = response.read().decode()
    return json.loads(response)

class MethodRequest(urllib2.Request):
    'See: https://gist.github.com/logic/2715756'
    def __init__(self, *args, **kwargs):
        if 'method' in kwargs:
            self._method = kwargs['method']
            del kwargs['method']
        else:
            self._method = None
        return urllib2.Request.__init__(self, *args, **kwargs)

    def get_method(self, *args, **kwargs):
        return self._method if self._method is not None else urllib2.Request.get_method(self, *args, **kwargs)

class RESTRequestHandler(BaseHTTPServer.BaseHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        self.routes = {
            r'^/$': {'file': 'web/index.php', 'media_type': 'text/html'},
            r'^/accounts$': {'GET': get_accounts, 'media_type': 'application/json'},
            r'^/account/': {'GET': get_account, 'PUT': set_account, 'DELETE': delete_account, 'media_type': 'application/json'},
            r'^/newaccount': {'GET': get_newaccount, 'PUT': set_newaccount, 'DELETE': delete_newaccount, 'media_type': 'application/json'},
            r'^/accountbalance/': {'GET': get_account_balance, 'PUT': set_account, 'DELETE': delete_account, 'media_type': 'application/json'},
            r'^/accountbalancedoge/': {'GET': get_account_balance_doge, 'PUT': set_account, 'DELETE': delete_account, 'media_type': 'application/json'},
            r'^/send001waves/': {'GET': send_001_waves, 'PUT': set_account, 'DELETE': delete_account, 'media_type': 'application/json'},
            r'^/senddogeback/': {'GET': send_doge_back, 'PUT': set_account, 'DELETE': delete_account, 'media_type': 'application/json'},
            r'^/senddogeuser/': {'GET': send_doge_user, 'PUT': set_account, 'DELETE': delete_account, 'media_type': 'application/json'},
            r'^/wavesbalance': {'GET': get_waves_balance, 'media_type': 'application/json'},
            r'^/dogebalance': {'GET': get_dogetoken_balance, 'media_type': 'application/json'},
            r'^/senddoge/': {'PUT': send_doge, 'media_type': 'application/json'},
            r'^/getwavesbalanceaddress/': {'GET': get_waves_balance_address, 'media_type': 'application/json'},
            r'^/generateaddress': {'GET': get_newwavesaddress, 'media_type': 'application/json'},
            }
        return BaseHTTPServer.BaseHTTPRequestHandler.__init__(self, *args, **kwargs)
    
    def do_HEAD(self):
        self.handle_method('HEAD')
    
    def do_GET(self):
        self.handle_method('GET')

    def do_POST(self):
        self.handle_method('POST')

    def do_PUT(self):
        self.handle_method('PUT')

    def do_DELETE(self):
        self.handle_method('DELETE')
    
    def get_payload(self):
        payload_len = int(self.headers.getheader('content-length', 0))
        payload = self.rfile.read(payload_len)
        payload = json.loads(payload)
        return payload
        
    def handle_method(self, method):
        route = self.get_route()
        if route is None:
            self.send_response(404)
            self.end_headers()
            self.wfile.write('Route not found\n')
        else:
            if method == 'HEAD':
                self.send_response(200)
                if 'media_type' in route:
                    self.send_header('Content-type', route['media_type'])
                self.end_headers()
            else:
                if 'file' in route:
                    if method == 'GET':
                        try:
                            f = open(os.path.join(here, route['file']))
                            try:
                                self.send_response(200)
                                if 'media_type' in route:
                                    self.send_header('Content-type', route['media_type'])
                                self.end_headers()
                                shutil.copyfileobj(f, self.wfile)
                            finally:
                                f.close()
                        except:
                            self.send_response(404)
                            self.end_headers()
                            self.wfile.write('File not found\n')
                    else:
                        self.send_response(405)
                        self.end_headers()
                        self.wfile.write('Only GET is supported\n')
                else:
                    if method in route:
                        content = route[method](self)
                        if content is not None:
                            self.send_response(200)
                            if 'media_type' in route:
                                self.send_header('Content-type', route['media_type'])
                            self.end_headers()
                            if method != 'DELETE':
                                self.wfile.write(json.dumps(content))
                        else:
                            self.send_response(404)
                            self.end_headers()
                            self.wfile.write('Not found\n')
                    else:
                        self.send_response(405)
                        self.end_headers()
                        self.wfile.write(method + ' is not supported\n')
                    
    
    def get_route(self):
        for path, route in self.routes.iteritems():
            if re.match(path, self.path):
                return route
        return None

def rest_server(port):
    'Starts the REST server'
    http_server = BaseHTTPServer.HTTPServer(('', port), RESTRequestHandler)
    print 'Starting HTTP server at port %d' % port
    try:
        http_server.serve_forever()
    except KeyboardInterrupt:
        pass
    print 'Stopping HTTP server'
    http_server.server_close()

def main(argv):
    rest_server(9966)

if __name__ == '__main__':
    main(sys.argv[1:])
