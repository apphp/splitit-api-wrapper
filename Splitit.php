<?php

/**
 * Super simple wrapper for SplitIt API BETA
 * SplitIt - Online Payment Solution Offer Installment Plans to Customers
 * Website: splitit.com
 * SplitIt API BETA: https://documenter.getpostman.com/view/795699/splitit-api-beta/2Qpqqj
 * 
 *
 * @author: Samuel Akopyan<admin@apphp.com>
 * @version: 1.3
 * @license: LGPL/MIT
 * @copyright: ApPHP
 * @link: https://github.com/apphp/splitit-api-wrapper	
 * @lastChanges: 04.02.2018
 *
 *
 * PUBLIC:					PRIVATE:
 * ----------------			----------------	
 * __construct				_makeRequest
 * login					_prepareStateForRequest
 * initiate					_setResponseState
 * create					_formatResponse
 * approve					_determineSuccess
 * startInstallments		_prepareParameters
 * updatePlan
 * cancel
 * refund
 * getVersion
 * get
 * getSessionId
 * getPlanNumber
 * getRefOrderNumber
 * getCheckoutUrl
 * getLastError				
 * getLastResponse			
 * getLastRequest			
 * 
 */

namespace Apphp\SplitIt;

class SplitIt
{
	const TIMEOUT				= 30;
	const SANDBOX_ENDPOINT_URL 	= 'https://web-api-sandbox.splitit.com';
	const ENDPOINT_URL			= 'https://web-api.splitit.com';
	const VERSION				= '1.3';

	private $_apiEndpoint 		= '';
	
	private $_mode				= 'sandbox';	/* sandbox or live */
	private $_apiKey			= null;
	private $_sessionId 		= null;
	private $_planNumber 		= null;
	private $_refOrderNumber	= null;
	private $_checkoutUrl 		= null;

	private $_requestSuccessful = false;
	private $_lastError			= '';
	private $_lastResponse		= array();
	private $_lastRequest		= array();
	
	private $_username			= '';
	private $_password			= '';
	

	/**
	 * Create a new instance
	 * @param array $config			Configuration array
	 * @param array $apiEndpoint	Endpoint for API
	 * @return void
	 * @throws \Exception
	 */
	public function __construct($config = array(), $apiEndpoint = null)
	{
		$this->_username = ! empty($config['username']) ? $config['username'] : '';
		$this->_password = ! empty($config['password']) ? $config['password'] : '';
		$this->_apiKey = ! empty($config['api_key']) ? $config['api_key'] : '';
		$this->_mode = ! empty($config['mode']) && $config['mode'] == 'live' ? 'live' : 'sandbox';
		
		if($apiEndpoint === null){
			if($this->_mode == 'live'){
				$this->_apiEndpoint = self::ENDPOINT_URL;
			}else{
				$this->_apiEndpoint = self::SANDBOX_ENDPOINT_URL;
			}
		}else{
			$this->_apiEndpoint = $apiEndpoint;
		}
		
		if($this->_apiEndpoint === null){
			throw new \Exception('Empty SplitIt API Endpoint supplied.');
		}
	}

	/**
	 * Login
	 * The authentication that allows your server\application to connect with SplitIt
	 * services by session returned from the Login service
	 *
	 * @param array $params
	 * @return array|false Assoc array of decoded result
	 * @throws \Exception
	 */
	public function login()
	{
		return $this->_makeRequest(__FUNCTION__, 'api/Login?format=json', $this->_prepareParameters(__FUNCTION__));
	}
	
	/**
	 * Initiate
	 * This method allows your server\application to start transaction with SplitIt
	 *
	 * @param array $params
	 * @return array|false Assoc array of decoded result
	 * @throws \Exception
	 */
	public function initiate($params = array())
	{
		return $this->_makeRequest(__FUNCTION__, 'api/InstallmentPlan/Initiate?format=json', $this->_prepareParameters(__FUNCTION__, $params));
	}
	
	/**
	 * Create
	 * This method allows your server\application complete transaction with SplitIt
	 * 
	 * @return array|false Assoc array of decoded result
	 * @throws \Exception
	 */
	public function create($params = array())
	{
		return $this->_makeRequest(__FUNCTION__, 'api/InstallmentPlan/Create?format=json', $this->_prepareParameters(__FUNCTION__, $params));
	}

	/**
	 * Approve
	 * This method allows you to indicate that shopper has approved the installment plan terms and conditions of SplitIt
	 *
	 * @param string $planNumber
	 * @return array|false Assoc array of decoded result
	 * @throws \Exception
	 */
	public function approve($planNumber = null)
	{
		$params = $this->_prepareParameters(__FUNCTION__);
		$params['InstallmentPlanNumber'] = ! empty($planNumber) ? $planNumber : $this->_planNumber;
		
		return $this->_makeRequest(__FUNCTION__, 'api/InstallmentPlan/Approve?format=json', $params);
	}
	
	/**
	 * StartInstallments
	 * This method indicate SplitIt to start running the first charge
	 *
	 * @param string $planNumber
	 * @return array|false Assoc array of decoded result
	 * @throws \Exception
	 */
	public function startInstallments($planNumber = null)
	{
		$params = $this->_prepareParameters(__FUNCTION__);
		$params['InstallmentPlanNumber'] = ! empty($planNumber) ? $planNumber : $this->_planNumber;
		
		return $this->_makeRequest(__FUNCTION__, 'api/InstallmentPlan/StartInstallments?format=json', $params);
	}
	
	/**
	 * UpdatePlan
	 * This method indicate SplitIt to start running the first charge
	 *
	 * @param string $planNumber
	 * @param string $refOrderNumber
	 * @param string $innerParams
	 * 				array('PlanData'=>array('Amount'=>1200, 'NumberOfInstallments'=>3), 'CreditCardDetails' => array(), ...) 
	 * @params array $extendedParams
	 * 				array('Comments' => 'This is VIP Customer') 
	 * @return array|false Assoc array of decoded result
	 * @throws \Exception
	 */
	public function updatePlan($planNumber = null, $refOrderNumber = null, $innerParams = array(), $extendedParams = array())
	{
		$params = $this->_prepareParameters(__FUNCTION__);
		$params['InstallmentPlanNumber'] = ! empty($planNumber) ? $planNumber : $this->_planNumber;
		
		if(empty($innerParams)){
			$params['PlanData'] = array(
				'RefOrderNumber' => ! empty($refOrderNumber) ? $refOrderNumber : $this->_refOrderNumber
			);			
		}
		else{
			$params = array_merge($params, $innerParams);
		}
		
		if(!empty($extendedParams)){
			$params['PlanData']['ExtendedParams'] = $extendedParams;
		}
		
		return $this->_makeRequest(__FUNCTION__, 'api/InstallmentPlan/Update?format=json', $params);
	}

	/**
	 * Cancel
	 * This method allows cancelling installment plan with or without refund
	 *
	 * @param string $planNumber
	 * @param string $refundUnderCancelation
	 * @return array|false Assoc array of decoded result
	 * @throws \Exception
	 */
	public function cancel($planNumber = null, $refundUnderCancelation = 'NoRefunds')
	{
		$params = $this->_prepareParameters(__FUNCTION__);
		$params['InstallmentPlanNumber'] = ! empty($planNumber) ? $planNumber : $this->_planNumber;
		$params['RefundUnderCancelation'] = $refundUnderCancelation;
		
		return $this->_makeRequest(__FUNCTION__, 'api/InstallmentPlan/Cancel?format=json', $params);
	}

	/**
	 * Refund
	 * This method allows partial or full refund in few different methods
	 * 
	 * @param string $planNumber
	 * @param string $amount
	 * @param string $refundStrategy		FutureInstallmentsFirst|FutureInstallmentsLast|FutureInstallmentsNotAllowed
	 * @return array|false Assoc array of decoded result
	 * @throws \Exception
	 */
	public function refund($planNumber = null, $amount = 0, $refundStrategy = 'NoRefunds')
	{
		$params = $this->_prepareParameters(__FUNCTION__);
		$params['InstallmentPlanNumber'] = ! empty($planNumber) ? $planNumber : $this->_planNumber;
		$params['Amount'] = array('Value' => $amount);
		$params['RefundStrategy'] = $refundStrategy;
		
		return $this->_makeRequest(__FUNCTION__, 'api/InstallmentPlan/Refund?format=json', $params);
	}
	
	/**
	 * GetVersion
	 * This method returns SplitIt class version
	 * 
	 * @return string
	 */
	public function getVersion()
	{
		return self::VERSION;
	}

	/**
	 * Get
	 * This method allows to get full installment plan data
	 * 
	 * @param string $planNumber
	 * @return array|false Assoc array of decoded result
	 * @throws \Exception
	 */
	public function get($planNumber = null)
	{
		$params = $this->_prepareParameters(__FUNCTION__);
		$params['QueryCriteria'] = array(
			'InstallmentPlanNumber' => ! empty($planNumber) ? $planNumber : $this->_planNumber,
		);
		
		return $this->_makeRequest(__FUNCTION__, 'api/InstallmentPlan/Get?format=json', $params);
	}
	
	/**
	 * Returns session ID
	 */
	public function getSessionId()
	{
		return $this->_sessionId ? $this->_sessionId : null;
	}

	/**
	 * Returns installment plan number
	 */
	public function getPlanNumber()
	{
		return $this->_planNumber ? $this->_planNumber : null;
	}
	
	/**
	 * Returns getRefOrderNumber
	 */
	public function getRefOrderNumber()
	{
		return $this->_refOrderNumber ? $this->_refOrderNumber : null;
	}

	/**
	 * Returns installment plan number
	 */
	public function getCheckoutUrl()
	{
		return $this->_checkoutUrl ? $this->_checkoutUrl : null;
	}
	
	/**
	 * Get the last error returned by either the network transport, or by the API.
	 * If something didn't work, this should contain the string describing the problem.
	 * @return  string|false  describing the error
	 */
	public function getLastError()
	{
		return $this->_lastError ? $this->_lastError : false;
	}

	/**
	 * Get an array containing the HTTP headers and the body of the API response.
	 * @return array  Assoc array with keys 'headers' and 'body'
	 */
	public function getLastResponse()
	{
		return $this->_lastResponse;
	}

	/**
	 * Get an array containing the HTTP headers and the body of the API request.
	 * @return array  Assoc array
	 */
	public function getLastRequest()
	{
		return $this->_lastRequest;
	}

	/**
	 * Performs the underlying HTTP request
	 * @param string $method
	 * @param string $requestUrl
	 * @param array $postFields
	 * @param int $timeout
	 * @return array|false Assoc array of decoded result
	 * @throws \Exception
	 */
	private function _makeRequest($method, $requestUrl, $postFields = array(), $timeout = self::TIMEOUT)
	{
		if(!function_exists('curl_init') || !function_exists('curl_setopt')){
			throw new \Exception("cURL support is required, but can't be found.");
		}
		
		$url = $this->_apiEndpoint.'/'.$requestUrl;
		
		$response = $this->_prepareStateForRequest($method, $url, $timeout);
		
		$ch = curl_init();
		
		curl_setopt_array($ch, array(
			CURLOPT_URL 			=> $url,
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_ENCODING 		=> "",
			CURLOPT_MAXREDIRS		=> 10,
			CURLOPT_TIMEOUT 		=> $timeout,
			CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST 	=> "POST",
			CURLOPT_POSTFIELDS 		=> json_encode($postFields),
			CURLOPT_HTTPHEADER 		=> array(
				"content-type: application/json"
			),
		));
		
		
		$responseContent		= curl_exec($ch);
		$response				= $this->_setResponseState($response, $responseContent, $postFields, $ch);
		$formattedResponse		= $this->_formatResponse($response);
		
		$this->_determineSuccess($response, $formattedResponse, $timeout);
		
		return $formattedResponse;
	}

	/**
	* @param string $method
	* @param string $url
	* @param integer $timeout
	*/
	private function _prepareStateForRequest($method, $url, $timeout)
	{
		$this->_lastError = '';
		
		$this->_requestSuccessful = false;

		$this->_lastResponse = array(
			'headers'	=> null, 	// array of details from curl_getinfo()
			'body'		=> null 	// content of the response
		);

		$this->_lastRequest = array(
			'url'	  => $url,
			'method'  => $method,
			'params'  => '',
			'timeout' => $timeout,
		);

		return $this->_lastResponse;
	}

	/**
	 * Do post-request formatting and setting state from the response
	 * @param array $response The response from the curl request
	 * @param string $responseContent The body of the response from the curl request
	 * @param array $postFields
	 * @return array	The modified response
	 */
	private function _setResponseState($response, $responseContent, $postFields, $ch)
	{
		$response['headers'] = curl_getinfo($ch);

		if($responseContent === false){
			$this->_lastError = curl_error($ch);
		}else{			
			$response['body'] = $responseContent;			
			$this->_lastRequest['params'] = $postFields;
		}

		return $response;
	}

	/**
	 * Decode the response and format any error messages for debugging
	 * @param array $response 	The response from the curl request
	 * @return array|false		The JSON decoded into an array
	 */
	private function _formatResponse($response)
	{
		if(!empty($response['body'])){
			$response['body'] = json_decode($response['body'], true);
		}
		
		$this->_lastResponse = $response;

		if($this->_lastRequest['method'] == 'login'){
			$this->_sessionId = ! empty($response['body']['SessionId']) ? $response['body']['SessionId'] : null;			
		}elseif($this->_lastRequest['method'] == 'initiate'){
			$this->_checkoutUrl = ! empty($response['body']['CheckoutUrl']) ? $response['body']['CheckoutUrl'] : null;			
			$this->_refOrderNumber = ! empty($response['body']['InstallmentPlan']['RefOrderNumber']) ? $response['body']['InstallmentPlan']['RefOrderNumber'] : null;
			$this->_planNumber = ! empty($response['body']['InstallmentPlan']['InstallmentPlanNumber']) ? $response['body']['InstallmentPlan']['InstallmentPlanNumber'] : null;
		}elseif($this->_lastRequest['method'] == 'create'){
			$this->_planNumber = ! empty($response['body']['InstallmentPlan']['InstallmentPlanNumber']) ? $response['body']['InstallmentPlan']['InstallmentPlanNumber'] : null;
		}
		
		if(isset($response['body']['ResponseHeader']['Succeeded']) && $response['body']['ResponseHeader']['Succeeded'] == false){
			$this->_lastError = $response['body']['ResponseHeader']['Errors'];
		}
		
		return $response;
	}

	/**
	 * Check if the response was successful or a failure. If it failed, store the error.
	 * @param array $response The response from the curl request
	 * @param array|false $formattedResponse The response body payload from the curl request
	 * @param int $timeout	The timeout supplied to the curl request.
	 * @return bool		If the request was successful
	 */
	private function _determineSuccess($response, $formattedResponse, $timeout)
	{
		$status = $this->_findHTTPStatus($response, $formattedResponse);
	
		if($status >= 200 && $status <= 299){
			$this->_requestSuccessful = true;
			return true;
		}

		if(isset($formattedResponse['detail'])){
			$this->_lastError = sprintf('%d: %s', $formattedResponse['status'], $formattedResponse['detail']);
			return false;
		}

		if( $timeout > 0 && $response['headers'] && $response['headers']['total_time'] >= $timeout ){
			$this->_lastError = sprintf('Request timed out after %f seconds.', $response['headers']['total_time'] );
			return false;
		}

		$this->_lastError = 'Unknown error, call getLastResponse() to find out what happened.';
		return false;
	}
	

	/**
	 * Find the HTTP status code from the headers or API response body
	 * @param array $response The response from the curl request
	 * @param array|false $formattedResponse The response body payload from the curl request
	 * @return int  HTTP status code
	 */
	private function _findHTTPStatus($response, $formattedResponse)
	{
		if(!empty($response['headers']) && isset($response['headers']['http_code'])){
			return (int)$response['headers']['http_code'];
		}

		if(!empty($response['body']) && isset($formattedResponse['status'])){
			return (int)$formattedResponse['status'];
		}
		
		return 418;
	}
	
	/**
	 * Prepares parameters for API call
	 * @param string $method
	 * @param array $params
	 * @return array
	 */
	private function _prepareParameters($method = '', $params = array())
	{
		$result = array();
		
		switch($method){
			case 'login':
				$result = array(
					'UserName'	=> $this->_username,
					'Password'	=> $this->_password
				);
				break;
			
			case 'get':
				$result['RequestHeader'] = array(
					'SessionId' => $this->_sessionId,
				);
				break;
			
			case 'initiate':
			case 'create':
			case 'approve':
			case 'startInstallments':
			case 'updatePlan':
			case 'cancel':
			case 'refund':	
				$result['RequestHeader'] = array(
					'SessionId' => $this->_sessionId,
					'ApiKey'	=> $this->_apiKey
				);
				break;			
		}
		
		if(! empty($params)){
			$result = array_merge($result, $params);
		}
		
		return $result;
	}
	
}
