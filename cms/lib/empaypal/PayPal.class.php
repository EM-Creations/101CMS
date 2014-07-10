<?php
/**
 * EM-Creations PayPal Class
 *
 * @author Edward McKnight (EM-Creations.co.uk)
 * @version 1.0
 */
class PayPal {
	// Class properties
	private $version = "94.0"; // PayPal Merchant API Version
	private $userName = false; // PayPal Merchant User Name
	private $password = false; // PayPal Merchant Password
	private $signature = false; // PayPal Merchant Signature
	private $connectionMode = false; // Connection mode, this is set in the constructor and can either be json or nvp
	//private $soapWSDL = false; // Property to store the WSDL file
	private $soapWSDL = "https://www.paypalobjects.com/wsdl/PayPalSvc.wsdl"; // Property to store the WSDL file
	private $endPoint = false; // Property to store the end point
	private $testMode = false; // Whether or not we're using test (sandbox) mode
	private $formElements = array(); // Property to store form elements
	private $url = false; // Property to store the PayPal URL
	private $timeout = false; // Connection timeout
	
	// Express Checkout Documentation (https://www.x.com/developers/paypal/documentation-tools/express-checkout/gs_expresscheckout)
	
	/**
	 * Create a new PayPal object
	 * 
	 * @param string $connectionMode
	 * @param boolean $testMode
	 * @param int $timeout
	 */
	public function __construct($userName, $password, $signature, $connectionMode = "soap", $testMode = false, $timeout = 5) {
		// <editor-fold defaultstate="collapsed" desc="Constructor Code">
		// Set credential class properties
		$this->userName = $userName;
		$this->password = $password;
		$this->signature = $signature;
		$this->timeout = (int) $timeout;
		
		if ($connectionMode == "soap") { // If we're using SOAP
			$this->connectionMode = "soap";

//			if (file_exists(__DIR__ . "/PayPal_" . $this->version . ".wsdl")) { // If the WSDL file exists locally
//				$this->soapWSDL = __DIR__ . "/PayPal_" . $this->version . ".wsdl";
//			} else { // If the WSDL file does not exist locally
//				$this->soapWSDL = "https://www.paypalobjects.com/wsdl/PayPalSvc.wsdl";
//			}
			
			if ($testMode) { // If we're using sandbox mode
				$this->testMode = true;
				$this->endPoint = "https://api-3t.sandbox.paypal.com/2.0/"; // Sandbox end point
				$this->url = "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout"; // Sandbox URL
			} else { // If we're using live mode
				$this->testMode = false;
				$this->endPoint = "https://api-3t.paypal.com/2.0/"; // Live end point
				$this->url = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout"; // Live URL
			}
		} else if ($connectionMode == "nvp") { // If we're using NVP (Name Value Pairs)
			$this->connectionMode = "nvp";
			
			if ($testMode) { // If we're using sandbox mode
				$this->testMode = true;
				$this->endPoint = "https://api-3t.sandbox.paypal.com/nvp"; // Sandbox end point
				$this->url = "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout"; // Sandbox URL
			} else { // If we're using live mode
				$this->testMode = false;
				$this->endPoint = "https://api-3t.paypal.com/nvp "; // Live end point
				$this->url = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout"; // Live URL
			}
		} else { // If the mode is invalid
			return false;
		}
		// </editor-fold>
	}
	
	/**
	 * Add form element to be used when outputting a payment form
	 * 
	 * @param string $element
	 * @param string $value
	 */
	public function addFormElement($element, $value) {
		$this->formElements[strip_tags($element)] = strip_tags($value);
	}
	
	/**
	 * Output HTML payment form
	 * 
	 */
	public function outputForm() {
		// <editor-fold defaultstate="collapsed" desc="Output Form Code">
		print("<h2>Redirecting to PayPal..</h2>\n");
		print("<form id=\"EM_PayPal_paymentForm\" method=\"post\" action=\"\">\n");
		foreach ($this->formElements as $key=>$val) {
			print("<input type=\"hidden\" name=\"" . $key . "\" value=\"" . $val . "\" />\n");
		}
		print("<button type=\"submit\">If you're browser does not automatically redirect within 10 seconds click this button</button>\n"); // Manual submit button
		print("</form>\n");
		
		// JavaScript to automatically submit the form
		print("<script type=\"text/javascript\">\n
			\t//<![CDATA[\n
			\twindow.onload = function () {\n
				\t\tvar t = setTimeout(\"document.getElementById(\"EM_PayPal_paymentForm\").submit();, 2000\"); // Wait two seconds before redirecting\n
			\t}\n
			\t//]]>\n
			</script>\n");
		// </editor-fold>
	}
	
	public function test() { // Test connecting and receiving a response
		if ($this->connectionMode == "nvp") {
			print("Using NVP<br />");
			
			// PAYMENTREQUEST_0_AMT must have the format (x)x.00, MUST HAVE .00 
			
			$params = array("PAYMENTREQUEST_0_AMT"=>"4.00",
				"PAYMENTREQUEST_0_CURRENCYCODE"=>"GBP", 
				"RETURNURL"=>"http://localhost/SVN/101cms/cms/lib/empaypal/?p=return",
				"CANCELURL"=>"http://localhost/SVN/101cms/cms/lib/empaypal/?p=cancel", 
				"PAYMENTREQUEST_0_PAYMENTACTION"=>"Sale");
			
			$result = $this->sendRequest("SetExpressCheckout", $params);
			
			if ($result['success']) {
				print_r($result['response']);
			} else {
				print($result['error']);
			}
			
			if ($result['response']['ACK'] == "Success") {
				$token = $result['response']['TOKEN'];
				
				header("Location: " . $this->url . "&token=" . $token);
				exit;
			} else {
				exit;
			}
			
		} else if ($this->connectionMode == "soap") {
			print("Using SOAP<br />");
			
			$params = array("PAYMENTREQUEST_0_AMT"=>4.00,
				"PAYMENTREQUEST_0_CURRENCYCODE"=>"GBP", 
				"RETURNURL"=>"http://localhost/SVN/101cms/cms/lib/empaypal/?p=return",
				"CANCELURL"=>"http://localhost/SVN/101cms/cms/lib/empaypal/?p=cancel", 
				"PAYMENTREQUEST_0_PAYMENTACTION"=>"Sale");
			
			$result = $this->sendRequest("SetExpressCheckout", $params);
			
			if ($result['success']) {
				print_r($result['response']);
			} else {
				print($result['error']);
			}
			
			if ($result['response']['ACK'] == "Success") {
				$token = $result['response']['TOKEN'];
				
				header("Location: " . $this->url . "&token=" . $token);
				exit;
			} else {
				exit;
			}
			
		}
	}
	
	/**
	 * Send cURL / SOAP request
	 * 
	 * @param string $method
	 * @param array $params
	 * @return array $result
	 */
	private function sendRequest($method, $params) {
		// <editor-fold defaultstate="collapsed" desc="Do Request Code">
		if ($this->connectionMode == "nvp") { // If we're using Name Value Pairs
			// <editor-fold defaultstate="collapsed" desc="Name Value Pairs Code">
			$params['METHOD'] = $method; // Add the method to the params
			$params['VERSION'] = $this->version; // Add the version number to the params
			$params['USER'] = $this->userName;
			$params['PWD'] = $this->password;
			$params['SIGNATURE'] = $this->signature;
			
			print_r($params); // Debugging params
			print("<br /><br />");
			
			// Use cURL to post the data
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $this->endPoint);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->timeout);
			//curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // This is potentially a security risk, that's a disadvantage of using cURL
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, count($params));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			
			$output = curl_exec($ch);
			
			$error = curl_error($ch);
			
			if (!empty($error)) { // If there was a cURL error
				$success = false;
			} else { // If there was not a cURL error
				$success = true;
			}
			
			curl_close($ch);
			
			if (!empty($output)) { // If the output is not empty
				$response = explode("&", $output); // Explode the response by the ampersand
				$finalResponse = array();
				
				foreach ($response as $res) { // For each response element
					$equalsPos = strpos($res, "="); // Find the position of the equals sign "="
					$key = substr($res, 0, $equalsPos); // Get the element key
					$value = substr($res, ($equalsPos + 1)); // Get the element value
					$value = urldecode($value); // Decode the value
					$finalResponse[$key] = $value; // Set the new element in the $finalResponse array
				}
			} else { // If the output is empty
				$finalResponse = "";
			}
			
			return array("success"=>$success, "response"=>$finalResponse, "error"=>$error);
			// </editor-fold>
		} else if ($this->connectionMode == "soap") { // If we're using SOAP
			// <editor-fold defaultstate="collapsed" desc="SOAP Code">
			
			$params['Version'] = $this->version; // Add the version number to the params
			$headers = array("Username"=>$this->userName,
							"Password"=>$this->password,
							"Signature"=>$this->signature); // Array of SOAP headers
			
			$error = "";
			
			try {
				$soap = new SoapClient($this->soapWSDL, array('trace'=>true, 'connection_timeout'=>5, 'cache_wsdl'=>WSDL_CACHE_NONE)); // Create a new SoapClient object
				
				$headerObjs = array(); // Array to store SOAP header objects
				foreach ($headers as $key=>$var) {
					$tempHeader = new SoapHeader("RequesterCredentials", $key, $var);
					$headerObjs[] = $tempHeader;
				}
				
				$soap->__setSoapHeaders($headerObjs);
				$output = $soap->__soapCall($method, $params);
				print($soap->__getLastRequestHeaders());
				$success = true;
			} catch (Exception $e) { // If there was an exception
				$error = $e->getMessage();
				$output = "";
				$success = false;
			}
			
			return array("success"=>$success, "response"=>$output, "error"=>$error);
			// </editor-fold>
		}
		// </editor-fold>
	}
}

?>