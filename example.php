<?php

	/**
	 * To run these examples get and insert username, password and api key.
	 * To get these parameters, you have to register on splitit.com
	 */
	
	use \Apphp\SplitIt\SplitIt;

	# Load library
	require('Splitit.php');
	
	# Create new object
	$config = array(
		'username' 	=> '<USERNAME>',
		'password' 	=> '<PASSWORD>',
		'api_key'	=> '<API-KEY>',
	);
	
	$splitit = new SplitIt($config);
	

	// LOGIN
	// ---------------------		
	$result = $splitit->login();
	if ( $error = $splitit->getLastError() ) {
		print_r('<br>Login: '.$error[0]['Message']);
		exit;
	}else{
		echo '<br>Logged!';
	}	
	
	// INITIATE
	// ---------------------
	$params = array(
		'PlanData'	=> array(
			'Amount' 			=> array('Value'=>99, 'CurrencyCode'=>'USD'),
			'RefOrderNumber'	=> 'XYZ',
			'AutoCapture'		=> true,
		),
		'BillingAddress' => array(
			'AddressLine'	=> '260 Madison Avenue.',
			'AddressLine2'	=> 'Appartment 1',
			'City'			=> 'New York',
			'State'			=> 'NY',
			'Country'		=> 'USA',
			'Zip'			=> '10016',
		),
		'ConsumerData' => array(
			'FullName'		=> 'John Smith',
			'Email'			=> 'j.smith@email.com',
			'PhoneNumber'	=> '1-844-775-4848',
			'CultureName'	=> 'en-us',
		),
		'PaymentWizardData' => array(
			'RequestedNumberOfInstallments' => '3,5,7',
			'SuccessExitURL' => 'http://www.yoursucessurl.com',
			'CancelExitURL' => 'http://www.yourcancelurl.com'				
		)
	);
	$result = $splitit->initiate($params);
	if ( $error = $splitit->getLastError() ) {
		print_r('<br>Initiate: '.$error[0]['Message']);
		exit;
	}else{
		echo '<br>Initiated! Checkout URL: '.$splitit->getCheckoutUrl();
	}
		

	// CREATE
	// ---------------------------
	$params = array(
		'PlanData'	=> array(
			'Amount' 			=> array('Value'=>99, 'CurrencyCode'=>'USD'),
			'RefOrderNumber	'	=> 'XYZ',
			'AutoCapture'		=> false,
			'NumberOfInstallments' => 3,
		),
		'BillingAddress' => array(
			'AddressLine'	=> '260 Madison Avenue.',
			'AddressLine2'	=> 'Appartment 1',
			'City'			=> 'New York',
			'State'			=> 'NY',
			'Country'		=> 'USA',
			'Zip'			=> '10016',
		),
		'ConsumerData' => array(
			'FullName'		=> 'John Smith - 2',
			'Email'			=> 'samuel@leibish.com',
			'PhoneNumber'	=> '1-844-775-4848',
			'CultureName'	=> 'en-us',
		),
		'CreditCardDetails' => array(
			'CardCvv'				=> '123',
			'CardHolderFullName'	=> 'John Smith',
			'CardNumber'			=> '4111111111111111',
			'CardExpYear'			=> '2019',
			'CardExpMonth'			=> '8',
		)
	);
	$result = $splitit->create($params);
	
	if ( $error = $splitit->getLastError() ) {
		print_r('<br>Create: '.$error[0]['Message']);
		exit;
	}else{
		echo '<br>Created! Plan #'.$splitit->getPlanNumber();
	}

	// APPROVE
	// ---------------------------
	$planNumber = $splitit->getPlanNumber();

	$result = $splitit->approve($planNumber);
	if ( $error = $splitit->getLastError() ) {
		print_r('<br>Approve: '.$error[0]['Message']);
		exit;
	}else{
		echo '<br>Plan approve #'.$splitit->getPlanNumber();
	}

	// START INSTALLMENTS
	// ---------------------------
	$result = $splitit->startInstallments($planNumber);
	if ( $error = $splitit->getLastError() ) {
		print_r('<br>Start installments: '.$error[0]['Message']);
		exit;
	}else{
		echo '<br>Plan started #'.$splitit->getPlanNumber();
	}

	// UPDATE PLAN
	// ---------------------------
	$refOrderNumber = $splitit->getRefOrderNumber();
	$params = array('PlanData' => array('RefOrderNumber' => $refOrderNumber), array('Comments' => 'This is VIP Customer'))

	$result = $splitit->updatePlan($planNumber, $refOrderNumber, $params);
	if ( $error = $splitit->getLastError() ) {
		print_r('<br>Update plan: '.$error[0]['Message']);
		exit;
	}else{
		echo '<br>Plan updated #'.$splitit->getPlanNumber();
	}

	// REFUND
	// ---------------------------
	$result = $splitit->refund($planNumber, 20);
	if ( $error = $splitit->getLastError() ) {
		print_r('<br>Refund: '.$error[0]['Message']);
		exit;
	}else{
		echo '<br>Plan refunded #'.$splitit->getPlanNumber();
	}

	// CANCEL
	// ---------------------------
	$result = $splitit->cancel($planNumber);
	if ( $error = $splitit->getLastError() ) {
		print_r('<br>Cancel: '.$error[0]['Message']);
		exit;
	}else{
		echo '<br>Plan canceled #'.$splitit->getPlanNumber();
	}

	// GET PLAN
	// ---------------------------
	$result = $splitit->get($planNumber);
	if ( $error = $splitit->getLastError() ) {
		print_r('<br>Get plan: '.$error[0]['Message']);
		exit;
	}else{
		echo '<br>Plan data received #'.$splitit->getPlanNumber();
	}

