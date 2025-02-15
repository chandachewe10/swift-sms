<!--
 API Documentation HTML Template  - 1.0.1
 Copyright © 2022 MACRO-IT
 Licensed under the MIT license.
 https://github.com/MACRO-IT/BULK-SMS-API.git
 !-->
 <!DOCTYPE html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <title>API - Documentation</title>
    <meta name="description" content="Bulk SMS - Send customized messages to a large audience quickly and efficiently. Enhance customer engagement, increase brand awareness, and drive conversions">
    <meta name="author" content="MACRO-IT">

    <meta http-equiv="cleartype" content="on">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Favicons -->
    <link href="{{asset('assets/img/favicon.png')}}" rel="icon">
    <link href="{{asset('assets/img/apple-touch-icon.png')}}" rel="apple-touch-icon">

    <link rel="stylesheet" href="css/hightlightjs-dark.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/9.8.0/highlight.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;1,300&family=Source+Code+Pro:wght@300&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{asset('docs/css/style.css')}}" media="all">
    <script>hljs.initHighlightingOnLoad();</script>
</head>

<body>
    <div class="left-menu">
        <div class="content-logo">
            <div class="logo">
                <img alt="platform by Emily van den Heever from the Noun Project"
                    title="platform by Emily van den Heever from the Noun Project" src="{{asset('docs/images/logo.png')}}" height="32" />
                <span>API Documentation</span>
            </div>
            <button class="burger-menu-icon" id="button-menu-mobile">
                <svg width="34" height="34" viewBox="0 0 100 100">
                    <path class="line line1"
                        d="M 20,29.000046 H 80.000231 C 80.000231,29.000046 94.498839,28.817352 94.532987,66.711331 94.543142,77.980673 90.966081,81.670246 85.259173,81.668997 79.552261,81.667751 75.000211,74.999942 75.000211,74.999942 L 25.000021,25.000058">
                    </path>
                    <path class="line line2" d="M 20,50 H 80"></path>
                    <path class="line line3"
                        d="M 20,70.999954 H 80.000231 C 80.000231,70.999954 94.498839,71.182648 94.532987,33.288669 94.543142,22.019327 90.966081,18.329754 85.259173,18.331003 79.552261,18.332249 75.000211,25.000058 75.000211,25.000058 L 25.000021,74.999942">
                    </path>
                </svg>
            </button>
        </div>
        <div class="mobile-menu-closer"></div>
        <div class="content-menu">
            <div class="content-infos">
                <div class="info"><b>Version:</b> 1.0.0</div>
                <div class="info"><b>Last Updated:</b> 06 June, 2023</div>
            </div>
            <ul>
                <li class="scroll-to-link active" data-target="content-get-started">
                    <a>GET STARTED</a>
                </li>
                <li class="scroll-to-link" data-target="cURL">
                    <a>cURL</a>
                </li>
                <li class="scroll-to-link" data-target="C-SHARP">
                    <a>C-Sharp</a>
                </li>
                <li class="scroll-to-link" data-target="JAVA">
                    <a>Java</a>
                </li>
                <li class="scroll-to-link" data-target="JAVASCRIPT">
                    <a>JavaScript</a>
                </li>
                <li class="scroll-to-link" data-target="OBJECTIVE-C">
                    <a>objective C</a>
                </li>
                <li class="scroll-to-link" data-target="PHP">
                    <a>PHP</a>
                </li>
                <li class="scroll-to-link" data-target="PYTHON">
                    <a>Python</a>
                </li>
                <li class="scroll-to-link" data-target="RUBY">
                    <a>Ruby</a>
                </li>
                <li class="scroll-to-link" data-target="SUCCESS">
                    <a>Success</a>
                </li>
                <li class="scroll-to-link" data-target="content-errors">
                    <a>Errors</a>
                </li>
            </ul>
        </div>
    </div>
    <div class="content-page">
        <div class="content-code"></div>
        <div class="content">
            <div class="overflow-hidden content-section" id="content-get-started">
                <h1>Get started</h1>
                <pre>
    API Endpoint:

        https://swift-sms.net/api/send_message

        Body Format: Raw
        {
            "sender_id": "MACRO-IT",
            "numbers": "0973750029,0769891754",
            "message": "Good afternoon"
          }
          
                </pre>
                <p>
                    Bulk SMS APIs are programming interfaces that allow developers to integrate bulk SMS functionality into their applications or systems. These APIs provide a set of methods and protocols that enable the sending and receiving of SMS messages in large quantities.
                </p>
                <p>
                    To use this in real world, you need a <strong>Bearer Token</strong> and a <strong>Sender Id</strong>. Please create an account with
                    us and make your token and sender Id in the developers dashboard.
                </p>
            </div>




            <!--cURL PHP API-->
            <div class="overflow-hidden content-section" id="cURL">
                <h2>get characters</h2>
                <pre><code class="bash">
# Here is a curl example
curl --location --request GET 'https://swift-sms.net/api/send_message' \
--header 'Content-Type: application/json' \
--header 'Accept: application/json' \
--header 'Authorization: Bearer PWoKyVBMIRh8wfQR2eCw8TOZ2MNmzCd7h9ikOSX2' \
--data-raw '{
  "sender_id": "MACRO-IT",
  "numbers": "00973750029,00769891754",
  "message": "Good afternoon"
}
'
               


                </code></pre>


             




                <h4>QUERY PARAMETERS</h4>
                <table class="central-overflow-x">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th>Method</th>
                        </tr>
                    </thead>
                    <tbody>
                       
                        <tr>
                            <td>Authorization</td>
                            <td>String</td>
                            <td>Your Bearer token.</td>
                            <td>GET</td>
                        </tr>
                        <tr>
                            <td>Content-Type</td>
                            <td>String</td>
                            <td>application/json</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Accept</td>
                            <td>String</td>
                            <td>application/json</td>
                            <td></td>
                        </tr>

                    </tbody>
                </table>



               



            </div>
            <!--cURL PHP API-->


            <!--C-SHARP API-->

            <div class="overflow-hidden content-section" id="C-SHARP">
                <h2>get characters</h2>
                <pre><code class="bash">
# Here is a c-sharp-restC# example
var client = new RestClient("https://swift-sms.net/api/send_message");
client.Timeout = -1;
var request = new RestRequest(Method.GET);
request.AddHeader("Content-Type", "application/json");
request.AddHeader("Accept", "application/json");
request.AddHeader("Authorization", "Bearer PWoKyVBMIRh8wfQR2eCw8TOZ2MNmzCd7h9ikOSX2");
var body = @"{
" + "\n" +
@"  ""sender_id"": ""MACRO-IT"",
" + "\n" +
@"  ""numbers"": ""0973750029,0769891754"",
" + "\n" +
@"  ""message"": ""Good afternoon""
" + "\n" +
@"}
" + "\n" +
@"";
request.AddParameter("application/json", body,  ParameterType.RequestBody);
IRestResponse response = client.Execute(request);
Console.WriteLine(response.Content);
            </code></pre>






          
                <h4>QUERY PARAMETERS</h4>
                <table class="central-overflow-x">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Authorization</td>
                            <td>String</td>
                            <td>Your Bearer token.</td>
                        </tr>
                        <tr>
                            <td>Content-Type</td>
                            <td>String</td>
                            <td>application/json</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Accept</td>
                            <td>String</td>
                            <td>application/json</td>
                            <td></td>
                        </tr>

                    </tbody>
                </table>


            </div>



            <!--END C-SHARP API-->



            <!--JAVA API-->

            <div class="overflow-hidden content-section" id="JAVA">
                <h2>get characters</h2>
                <pre><code class="bash">
# Here is a JAVA Http example

OkHttpClient client = new OkHttpClient().newBuilder()
  .build();
MediaType mediaType = MediaType.parse("application/json");
RequestBody body = RequestBody.create(mediaType, "{\r\n  \"sender_id\": \"MACRO-IT\",\r\n  \"numbers\": \"00973750029,00769891754\",\r\n  \"message\": \"Good afternoon\"\r\n}\r\n");
Request request = new Request.Builder()
  .url("https://swift-sms.net/api/send_message")
  .method("GET", body)
  .addHeader("Content-Type", "application/json")
  .addHeader("Accept", "application/json")
  .addHeader("Authorization", "Bearer PWoKyVBMIRh8wfQR2eCw8TOZ2MNmzCd7h9ikOSX2")
  .build();
Response response = client.newCall(request).execute();
        </code></pre>








             
                <h4>QUERY PARAMETERS</h4>
                <table class="central-overflow-x">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Authorization</td>
                            <td>String</td>
                            <td>Your Bearer token.</td>
                        </tr>
                        <tr>
                            <td>Content-Type</td>
                            <td>String</td>
                            <td>application/json</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Accept</td>
                            <td>String</td>
                            <td>application/json</td>
                            <td></td>
                        </tr>

                    </tbody>
                </table>


            </div>



            <!--END JAVA API-->





            <!--JAVASCRIPT API-->

            <div class="overflow-hidden content-section" id="JAVASCRIPT">
                <h2>get characters</h2>
                <pre><code class="bash">
# Here is a Js example
// WARNING: For GET requests, body is set to null by browsers.
var data = JSON.stringify({
  "sender_id": "MACRO-IT",
  "numbers": "00973750029,00769891754",
  "message": "Good afternoon"
});

var xhr = new XMLHttpRequest();
xhr.withCredentials = true;

xhr.addEventListener("readystatechange", function() {
  if(this.readyState === 4) {
    console.log(this.responseText);
  }
});

xhr.open("GET", "https://swift-sms.net/api/send_message");
xhr.setRequestHeader("Content-Type", "application/json");
xhr.setRequestHeader("Accept", "application/json");
xhr.setRequestHeader("Authorization", "Bearer PWoKyVBMIRh8wfQR2eCw8TOZ2MNmzCd7h9ikOSX2");
xhr.send(data);
            </code></pre>








             
                <h4>QUERY PARAMETERS</h4>
                <table class="central-overflow-x">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Authorization</td>
                            <td>String</td>
                            <td>Your Bearer token.</td>
                        </tr>
                        <tr>
                            <td>Content-Type</td>
                            <td>String</td>
                            <td>application/json</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Accept</td>
                            <td>String</td>
                            <td>application/json</td>
                            <td></td>
                        </tr>

                    </tbody>
                </table>


            </div>



            <!--JAVASCRIPT-->




            <!--OBJECTIVE C-->

            <div class="overflow-hidden content-section" id="OBJECTIVE-C">
                <h2>get characters</h2>
                <pre><code class="bash">
# Here is the OBJECTIVE-C Example
#import <Foundation/Foundation.h>

dispatch_semaphore_t sema = dispatch_semaphore_create(0);

NSMutableURLRequest *request = [NSMutableURLRequest requestWithURL:[NSURL URLWithString:@"https://swift-sms.net/api/send_message"]
  cachePolicy:NSURLRequestUseProtocolCachePolicy
  timeoutInterval:10.0];
NSDictionary *headers = @{
  @"Content-Type": @"application/json",
  @"Accept": @"application/json",
  @"Authorization": @"Bearer PWoKyVBMIRh8wfQR2eCw8TOZ2MNmzCd7h9ikOSX2"
};

[request setAllHTTPHeaderFields:headers];
NSData *postData = [[NSData alloc] initWithData:[@"{\r\n  \"sender_id\": \"MACRO-IT\",\r\n  \"numbers\": \"00973750029,00769891754\",\r\n  \"message\": \"Good afternoon\"\r\n}\r\n" dataUsingEncoding:NSUTF8StringEncoding]];
[request setHTTPBody:postData];

[request setHTTPMethod:@"GET"];

NSURLSession *session = [NSURLSession sharedSession];
NSURLSessionDataTask *dataTask = [session dataTaskWithRequest:request
completionHandler:^(NSData *data, NSURLResponse *response, NSError *error) {
  if (error) {
    NSLog(@"%@", error);
    dispatch_semaphore_signal(sema);
  } else {
    NSHTTPURLResponse *httpResponse = (NSHTTPURLResponse *) response;
    NSError *parseError = nil;
    NSDictionary *responseDictionary = [NSJSONSerialization JSONObjectWithData:data options:0 error:&parseError];
    NSLog(@"%@",responseDictionary);
    dispatch_semaphore_signal(sema);
  }
}];
[dataTask resume];
dispatch_semaphore_wait(sema, DISPATCH_TIME_FOREVER);
            </code></pre>








              </h4>
                <table class="central-overflow-x">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Authorization</td>
                            <td>String</td>
                            <td>Your Bearer token.</td>
                        </tr>
                        <tr>
                            <td>Content-Type</td>
                            <td>String</td>
                            <td>application/json</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Accept</td>
                            <td>String</td>
                            <td>application/json</td>
                            <td></td>
                        </tr>

                    </tbody>
                </table>


            </div>



            <!--OBJECTIVE-C-->



            <!--PHP API-->

            <div class="overflow-hidden content-section" id="PHP">
                <h2>get characters</h2>
                <pre><code class="bash">
# Here is a PHP cURL example

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://swift-sms.net/api/send_message',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_POSTFIELDS =>'{
  "sender_id": "MACRO-IT",
  "numbers": "0973750029,0769891754",
  "message": "Good afternoon"
}
',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Accept: application/json',
    'Authorization: Bearer PWoKyVBMIRh8wfQR2eCw8TOZ2MNmzCd7h9ikOSX2'
  ),
));

$response = curl_exec($curl);

curl_close($curl);
echo $response;

            </code></pre>


              
                <h4>QUERY PARAMETERS</h4>
                <table class="central-overflow-x">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Type</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Authorization</td>
                            <td>String</td>
                            <td>Your Bearer token.</td>
                        </tr>
                        <tr>
                            <td>Content-Type</td>
                            <td>String</td>
                            <td>application/json</td>
                            <td></td>
                        </tr>
                        <tr>
                            <td>Accept</td>
                            <td>String</td>
                            <td>application/json</td>
                            <td></td>
                        </tr>

                    </tbody>
                </table>


            </div>



            <!--PHP API-->





            <!--PYTHON API-->

            <div class="overflow-hidden content-section" id="PYTHON">
                <h2>get characters</h2>
                <pre><code class="bash">
# Here is a PYTHON Http example
import http.client
import json

conn = http.client.HTTPSConnection("swift-sms.net", )
payload = json.dumps({
  "sender_id": "MACRO-IT",
  "numbers": "00973750029,00769891754",
  "message": "Good afternoon"
})
headers = {
  'Content-Type': 'application/json',
  'Accept': 'application/json',
  'Authorization': 'Bearer PWoKyVBMIRh8wfQR2eCw8TOZ2MNmzCd7h9ikOSX2'
}
conn.request("GET", "/api/send_message", payload, headers)
res = conn.getresponse()
data = res.read()
print(data.decode("utf-8"))


            </code></pre>
            <h4>QUERY PARAMETERS</h4>
            <table class="central-overflow-x">
                <thead>
                    <tr>
                        <th>Field</th>
                        <th>Type</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Authorization</td>
                        <td>String</td>
                        <td>Your Bearer token.</td>
                    </tr>
                    <tr>
                        <td>Content-Type</td>
                        <td>String</td>
                        <td>application/json</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td>Accept</td>
                        <td>String</td>
                        <td>application/json</td>
                        <td></td>
                    </tr>

                </tbody>
            </table>


            </div>



            <!--PYTHON API-->



            <!--RUBY API-->

            <div class="overflow-hidden content-section" id="RUBY">
                <h2>get characters</h2>
                <pre><code class="bash">
# Here is a RUBY example
require "uri"
require "json"
require "net/http"

url = URI("https://swift-sms.net/api/send_message")

http = Net::HTTP.new(url.host, url.port);
request = Net::HTTP::Get.new(url)
request["Content-Type"] = "application/json"
request["Accept"] = "application/json"
request["Authorization"] = "Bearer PWoKyVBMIRh8wfQR2eCw8TOZ2MNmzCd7h9ikOSX2"
request.body = JSON.dump({
  "sender_id": "MACRO-IT",
  "numbers": "0973750029,0769891754",
  "message": "Good afternoon"
})

response = http.request(request)
puts response.read_body


    
        </code></pre>
        <h4>QUERY PARAMETERS</h4>
        <table class="central-overflow-x">
            <thead>
                <tr>
                    <th>Field</th>
                    <th>Type</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Authorization</td>
                    <td>String</td>
                    <td>Your Bearer token.</td>
                </tr>
                <tr>
                    <td>Content-Type</td>
                    <td>String</td>
                    <td>application/json</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Accept</td>
                    <td>String</td>
                    <td>application/json</td>
                    <td></td>
                </tr>

            </tbody>
        </table>


            </div>



            <!--RUBY API-->


<!-- Success Codes -->
<div class="overflow-hidden content-section" id="SUCCESS">
    <h2>Success</h2>
    <p>
        The SWIFT-SMS API uses the following success status codes:
    </p>
    <table>
        <thead>
            <tr>
                <th>Status Code</th>
                <th>Meaning</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>202</td>
                <td>
                    Accepted. Request Accepted For Delivery
                </td>
            </tr>
            

        </tbody>
    </table>
</div>









            <!--Get Errors-->
            <div class="overflow-hidden content-section" id="content-errors">
                <h2>Errors</h2>
                <p>
                    The SWIFT-SMS API uses the following error codes:
                </p>
                <table>
                    <thead>
                        <tr>
                            <th>Error Code</th>
                            <th>Meaning</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>401</td>
                            <td>
                                Unauthenticated
                            </td>
                        </tr>
                        <tr>
                            <td>422</td>
                            <td>
                                Unprocessable entity
                            </td>
                        </tr>
                        <tr>
                            <td>500</td>
                            <td>
                                INTERNAL_PROCESSING_ERROR
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
        <div class="content-code"></div>
    </div>
    <!-- Github Corner Ribbon - to remove (Ribbon created with : http://tholman.com/github-corners/ )-->
    <a href="https://github.com/MACRO-IT/BULK-SMS-API.git" class="github-corner"
        aria-label="View source on Github" title="Contribute on Github"><svg width="80" height="80"
            viewBox="0 0 250 250"
            style="z-index:99999; fill:#70B7FD; color:#fff; position: fixed; top: 0; border: 0; right: 0;"
            aria-hidden="true">
            <path d="M0,0 L115,115 L130,115 L142,142 L250,250 L250,0 Z"></path>
            <path
                d="M128.3,109.0 C113.8,99.7 119.0,89.6 119.0,89.6 C122.0,82.7 120.5,78.6 120.5,78.6 C119.2,72.0 123.4,76.3 123.4,76.3 C127.3,80.9 125.5,87.3 125.5,87.3 C122.9,97.6 130.6,101.9 134.4,103.2"
                fill="currentColor" style="transform-origin: 130px 106px;" class="octo-arm"></path>
            <path
                d="M115.0,115.0 C114.9,115.1 118.7,116.5 119.8,115.4 L133.7,101.6 C136.9,99.2 139.9,98.4 142.2,98.6 C133.8,88.0 127.5,74.4 143.8,58.0 C148.5,53.4 154.0,51.2 159.7,51.0 C160.3,49.4 163.2,43.6 171.4,40.1 C171.4,40.1 176.1,42.5 178.8,56.2 C183.1,58.6 187.2,61.8 190.9,65.4 C194.5,69.0 197.7,73.2 200.1,77.6 C213.8,80.2 216.3,84.9 216.3,84.9 C212.7,93.1 206.9,96.0 205.4,96.6 C205.1,102.4 203.0,107.8 198.3,112.5 C181.9,128.9 168.3,122.5 157.7,114.1 C157.9,116.9 156.7,120.9 152.7,124.9 L141.0,136.5 C139.8,137.7 141.6,141.9 141.8,141.8 Z"
                fill="currentColor" class="octo-body"></path>
        </svg></a>
    <style>
        .github-corner:hover .octo-arm {
            animation: octocat-wave 560ms ease-in-out
        }

        @keyframes octocat-wave {

            0%,
            100% {
                transform: rotate(0)
            }

            20%,
            60% {
                transform: rotate(-25deg)
            }

            40%,
            80% {
                transform: rotate(10deg)
            }
        }

        @media (max-width:500px) {
            .github-corner:hover .octo-arm {
                animation: none
            }

            .github-corner .octo-arm {
                animation: octocat-wave 560ms ease-in-out
            }
        }

        @media only screen and (max-width:680px) {
            .github-corner>svg {
                right: auto !important;
                left: 0 !important;
                transform: rotate(270deg) !important;
            }
        }
    </style>
    <!-- END Github Corner Ribbon - to remove -->
    <script src="{{asset('docs/js/script.js')}}"></script>
</body>

</html>