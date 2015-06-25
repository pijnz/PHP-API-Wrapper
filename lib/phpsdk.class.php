<?php 
/**
 * PHP API interface
 * 
 * Change date: 2014-12-05
 * 
 */
class ApiV2 {
	
	protected $apiKey = '';
	protected $apiURL = 'https://files.safemobi.net/rest/v2'; 
	protected $accessToken = '';
	protected $saveTokenCallback;
	protected $loadTokenCallback;
	protected $tmpTokenFilename;

	
	
	/**
	 * GET accountplans/{accountid}?accounttype={accounttype}
	 * 
	 * Returns the list of Basic or Premium account plans
	 * 
	 * Required parameters:
	 * $accounttype - Requested account plan type.
	 * 		Possible values: Basic, Premium.
	 * 		Note: access to create Premium accounts is not available by default. If you don't have that access, please contact our support for details on how to enable Premium accounts under your main account.
	 * 
	 * @param string $accounttype
	 * @param string $accountid
	 * return apiResponse object
	 */
	public function getAccountPlans($accounttype='Basic', $accountID='') {
		if ($accountID) {
			$res = $this->ApiRequest('/accountplans/'.$accountID.'?accounttype='.$accounttype);
		} else {
			 $res = $this->ApiRequest('/accountplans/?accounttype='.$accounttype);
		}
		if ($res->Success && is_array($res->Data)) :
			for ($i=0;$i<count($res->Data);$i++) :
				if (is_object($res->Data[$i]->BasicAccountPlanInfo)) $res->Data[$i]->BasicAccountPlanInfo = recast('BasicAccountPlanInfo', $res->Data[$i]->BasicAccountPlanInfo);
				if (is_object($res->Data[$i]->PremiumAccountPlanInfo)) $res->Data[$i]->PremiumAccountPlanInfo = recast('PremiumAccountPlanInfo', $res->Data[$i]->PremiumAccountPlanInfo);
				$res->Data[$i] = recast('AccountPlan', $res->Data[$i]);
			endfor;
		endif;
		return $res;
	}
	
	/* LoyaltyProgram
	 * API methods for working with the loyalty program
	 */
	
	/**
	 * PUT loyaltyprogram/members/{accountid}
	 * 
	 * Register a loyalty program member
	 * 
	 * Required parameters:
	 * $accountid - Your account id
	 * $member    - New loyalty program member
	 * 				Array of values, or ApiLPMember object, or stdClass object can be used
	 * 
	 * @param $member
	 * @param string $accountid
	 * @return apiResponse object
	 */
	public function addLoyaltyMember($member, $accountID='') {
		$res = $this->ApiRequest('/loyaltyprogram/members/'.$accountID, 'PUT', new ApiLPMember($member));
		if ($res->Success) $res->Data = recast('ApiLPMember', $res->Data);
		return $res;
	}
	
	/**
	 * POST loyaltyprogram/members/{accountid}
	 * 
	 * Update a loyalty program member
	 * 
	 * Required parameters:
	 * $accountID - Your account id
	 * $member    - Loyalty program member
	 * 				Array of values, or ApiLPMember object, or stdClass object can be used
	 * 
	 * @param $member
	 * @param string $accountID
	 * @return apiResponse object
	 */
	public function updateLoyaltyMember($member, $accountID='') {
		$res =  $this->ApiRequest('/loyaltyprogram/members/'.$accountID, 'POST', new ApiLPMember($member));
		if ($res->Success) $res->Data = recast('ApiLPMember', $res->Data);
		return $res;
	}
	
	/**
	 * GET loyaltyprogram/members/{accountid}/{memberid}
	 * 
	 * Returns a member object containing the member information
	 * 
	 * Required parameters:
	 * $accountID - Your account id
	 * $memberid - Member id
	 * 
	 * @param string $accountID
	 * @param string $memberid
	 * @return apiResponse object
	 */
	public function getLoyaltyMember($accountID, $memberid) {
		$res = $this->ApiRequest('/loyaltyprogram/members/'.$accountID.'/'.$memberid);
		if ($res->Success) $res->Data = recast('ApiLPMember', $res->Data);
		return $res;
	}
	
	/**
	 * GET loyaltyprogram/members/{accountid}?skip={skip}&take={take}
	 * 
	 * Returns a list of members within a loyalty program
	 * 
	 * Required parameters:
	 * $accountID - Your account id
	 * 
	 * Optional parameters:
	 * $skip - Skip record count. Default value is 0
	 * $take - Take record count. Default value is 500
	 * 
	 * @param string $accountID
	 * @param int $skip
	 * @param int $take
	 * @return apiResponse object
	 */
	public function getLoyaltyMembers($accountID, $skip=0, $take=500){
		$res =  $this->ApiRequest('/loyaltyprogram/members/'.$accountID.'?skip='.$skip.'&take='.$take);
		if ($res->Success) :
			for ($i=0;$i<count($res->Data->Items);$i++) :
				$res->Data->Items[$i] = recast('ApiLPMember', $res->Data->Items[$i]);
			endfor;
			$res->Data = recast('PageListOfApiLPMember', $res->Data);
		endif;
		return $res;
	}
	
	/**
	 * GET loyaltyprogram/employees/{accountid}?skip={skip}&take={take}
	 * 	
	 * Returns a list of authorized employees within a loyalty program
	 *
	 * Required parameters:
	 * $accountID - Your account id
	 * 
	 * Optional parameters:
	 * $skip - Skip record count. Default value is 0
	 * $take - Take record count. Default value is 500
	 * 
	 * @param string $accountID
	 * @param int $skip
	 * @param int $take
	 * @return apiResponse object
	 */
	public function getLoyaltyEmployees($accountID, $skip=0, $take=500){
		$res =  $this->ApiRequest('/loyaltyprogram/employees/'.$accountID.'?skip='.$skip.'&take='.$take);
		if ($res->Success) :
			for ($i=0;$i<count($res->Data->Items);$i++) :
				$res->Data->Items[$i] = recast('ApiLPEmployee', $res->Data->Items[$i]);
			endfor;
			$res->Data = recast('PageListOfApiLPEmployee', $res->Data);
		endif;
		return $res;
	}
	
	
	/**
	 * GET loyaltyprogram/actions/{accountid}
	 * 
	 * Returns a list of bonus actions configured for a loyalty program
	 *
	 * Required parameters:
	 * $accountID - Your account id
	 * 
	 * @param string $accountID
	 * @return apiResponse object
	 */
	public function getLoyaltyBonusActions($accountID){
		$res =  $this->ApiRequest('/loyaltyprogram/actions/'.$accountID);
		if ($res->Success) :
			for ($i=0;$i<count($res->Data->Items);$i++) :
				$res->Data->Items[$i] = recast('ApiLPActionInfo', $res->Data->Items[$i]);
			endfor;
		endif;
		return $res;
	}
	
	/**
	 * GET loyaltyprogram/rewards/{accountid}
	 * 
	 * Returns a list of rewards configured for a loyalty program
	 *
	 * Required parameters:
	 * $accountID - Your account id
	 * 
	 * @param string $accountID
	 * @return apiResponse object
	 */
	public function getLoyaltyRewards($accountID){
		$res =  $this->ApiRequest('/loyaltyprogram/rewards/'.$accountID);
		if ($res->Success) :
			for ($i=0;$i<count($res->Data->Items);$i++) :
				$res->Data->Items[$i] = recast('ApiLPRewardInfo', $res->Data->Items[$i]);
			endfor;
		endif;
		return $res;
	}
	
	/**
	 * POST loyaltyprogram/enterpoints/{accountid}?isPunch={isPunch}
	 * 
	 * Enter points for a loyalty program member
	 * 
	 * Required parameters:
	 * $accountID - Your account id
	 * $transaction - transaction. Can be array, stdClass or ApiLPTransaction object 
	 * 
	 * Optional Parameters:
	 * $isPunch - Default value is False
	 * 
	 * @param string $accountID
	 * @param $transaction
	 * @param bool $isPunch
	 * @return apiResponse object
	 */
	 
	public function LoyaltyEnterPoints($accountID, $transaction,  $isPunch = false){
		$res =  $this->ApiRequest('/loyaltyprogram/enterpoints/'.$accountID.'?isPunch='.($isPunch ? 'true' : 'false'), 'POST', new ApiLPTransaction($transaction));
		if ($res->Success) $res->Data = recast('ApiLPTransaction', $res->Data);
		return $res;
	}
	
	/**
	 * POST loyaltyprogram/enteraction/{accountid}
	 * 
	 * Record a bonus action for a loyalty program member
	 *
	 * Required parameters:
	 * $accountID - Your account id
	 * $transaction - Action transaction. Can be array, stdClass or ApiLPActionTransaction object
	 *  
	 * @param string $accountID
	 * @param $transaction
	 * @return apiResponse object
	 */
	public function LoyaltyEnterAction($accountID, $transaction){
		$res =  $this->ApiRequest('/loyaltyprogram/enteraction/'.$accountID, 'POST', new ApiLPActionTransaction($transaction));
		if ($res->Success) $res->Data = recast('ApiLPActionTransaction', $res->Data);
		return $res;
	}
	
	/**
	 * POST loyaltyprogram/enterredemption/{accountid}
	 * 
	 * Record a reward redemption for a loyalty program member
	 *
	 * Required parameters:
	 * $accountID - Your account id
	 * $transaction - Action transaction. Can be array, stdClass or ApiLPActionTransaction object
	 *  
	 * @param string $accountID
	 * @param $transaction
	 * @return apiResponse object
	 */
	public function LoyaltyEnterRedemption($accountID, $transaction){
		$res =  $this->ApiRequest('/loyaltyprogram/enterredemption/'.$accountID, 'POST', new ApiLPRedemptionTransaction($transaction));
		if ($res->Success) $res->Data = recast('ApiLPRedemptionTransaction', $res->Data);
		return $res;
	}
	
	/* Account functions
	 * API methods for working with accounts
	 *  */
	
	
	/**
	 * PUT accounts
	 * 
	 * Creates an account in our platform and returns an Account object with account id
	 *
	 * Required parameters:
	 * $account - Account model. Can be array, stdClass or Account object
	 * 
	 * @param $account
	 * @return apiResponse object
	 */
	public function addAccount($account) {
		$acc = new Account($account);
		if ($acc->Type == 'Premium') {
			$acc->PremiumAccountInfo = recast('PremiumAccountInfo', $acc->PremiumAccountInfo);
		}
		$res = $this->ApiRequest('/accounts', 'PUT', $acc);
		if ($res->Success) {
			$res->Data = recast('Account', $res->Data);
			if ($res->Data->Type == 'Premium') $res->Data->PremiumAccountInfo = recast('PremiumAccountInfo', $res->Data->PremiumAccountInfo);
		}
		return $res;
	}
	
	/**
	 * GET accounts?skip={skip}&take={take}
	 * 
	 * Returns the list of Basic and Premium accounts with sub-accounts and sites.
	 * Note: for efficiency this API method can only return up to 500 records at a time.
	 * Use the skip and take parameters to return more records.
	 *
	 * Optional Parameters:
	 * $skip - Default value is 0
	 * $take - Default value is 500. Range: inclusive between 1 and 500
	 * 
	 * @param int $skip
	 * @param int $take
	 * @return apiResponse object
	 */
	public function getAccounts($skip=0, $take=500) {
		$res = $this->ApiRequest('/accounts?skip='.$skip.'&take='.$take);
		if ($res->Success && is_array($res->Data->Items)) :
			for ($i=0;$i<count($res->Data->Items);$i++) :
					$res->Data->Items[$i] = recast('Account', $res->Data->Items[$i]);
					if (is_array($res->Data->Items[$i]->Sites)) :
						for ($j=0;$j<count($res->Data->Items[$i]->Sites);$j++) :
							$res->Data->Items[$i]->Sites[$j] = recast('Site', $res->Data->Items[$i]->Sites[$j]);
						endfor;
					endif;
					if (is_array($res->Data->Items[$i]->SubAccounts)) :
						for ($j=0;$j<count($res->Data->Items[$i]->SubAccounts);$j++) :
							$res->Data->Items[$i]->SubAccounts[$j] = recast('Account', $res->Data->Items[$i]->SubAccounts[$j]);
						endfor;
					endif;
					if (is_object($res->Data->Items[$i]->PremiumAccountInfo)) :
						$res->Data->Items[$i]->PremiumAccountInfo = recast('PremiumAccountInfo', $res->Data->Items[$i]->PremiumAccountInfo);
					endif;
			endfor;
			$res->Data = recast('PageListOfAccount', $res->Data);
		endif;
		return $res;
	}
	
	/**
	 * GET accounts?externalid={externalid}&skip={skip}&take={take}
	 * 
	 * Returns the list of Basic and Premium accounts for a given external id with sub-accounts and sites. 
	 * Note: for efficiency this API method can only return up to 500 records at a time. 
	 * Use the skip and take parameters to return more records.
	 * 
	 * Optional Parameters:
	 * $externalid - Id for this account in your system. This parameter can be empty, and in this case the method returns all accounts (use /accounts/ format if this parameter is empty). Max length: 250
	 * $skip - Default value is 0
	 * $take - Default value is 500. Range: inclusive between 1 and 500
	 *
	 * @param string $externalid
	 * @param int $skip
	 * @param int $take
	 * @return apiResponse object
	 */
	public function getAccountsByExternalId($externalid='', $skip=0, $take=500) {
		$res = $this->ApiRequest('/accounts?externalid='.$externalid.'&skip='.$skip.'&take='.$take);
		if ($res->Success && is_array($res->Data->Items)) :
			for ($i=0;$i<count($res->Data->Items);$i++) :
				$res->Data->Items[$i] = recast('Account', $res->Data->Items[$i]);
				if (is_array($res->Data->Items[$i]->Sites)) :
					for ($j=0;$j<count($res->Data->Items[$i]->Sites);$j++) :
						$res->Data->Items[$i]->Sites[$j] = recast('Site', $res->Data->Items[$i]->Sites[$j]);
					endfor;
				endif;
				if (is_array($res->Data->Items[$i]->SubAccounts)) :
					for ($j=0;$j<count($res->Data->Items[$i]->SubAccounts);$j++) :
						$res->Data->Items[$i]->SubAccounts[$j] = recast('Account', $res->Data->Items[$i]->SubAccounts[$j]);
					endfor;
				endif;
				if (is_object($res->Data->Items[$i]->PremiumAccountInfo)) :
					$res->Data->Items[$i]->PremiumAccountInfo = recast('PremiumAccountInfo', $res->Data->Items[$i]->PremiumAccountInfo);
				endif;
			endfor;
			$res->Data = recast('PageListOfAccount', $res->Data);
		endif;
		return $res;
	}
	
	
	/**
	 * GET accounts/{accountid}
	 * 
	 * Returns the information on the account
	 * 
	 * Required parameters:
	 * $accountID - Account id
	 *
	 * @param string $accountID
	 * @return apiResponse object
	 */
	public function getAccount($accountID) {
		$res = $this->ApiRequest('/accounts/'.$accountID);
		if ($res->Success)	:
			$res->Data = recast('Account', $res->Data);
			if (is_array($res->Data->Sites)) :
				for ($j=0;$j<count($res->Data->Sites);$j++) :
					$res->Data->Sites[$j] = recast('Site', $res->Data->Sites[$j]);
				endfor;
			endif;
			if (is_array($res->Data->SubAccounts)) :
				for ($j=0;$j<count($res->Data->SubAccounts);$j++) :
					$res->Data->SubAccounts[$j] = recast('Account', $res->Data->SubAccounts[$j]);
				endfor;
			endif;
			if (is_object($res->Data->PremiumAccountInfo)) :
				$res->Data->PremiumAccountInfo = recast('PremiumAccountInfo', $res->Data->PremiumAccountInfo);
			endif;
		endif;
		return $res;
	}

	/**
	 * GET accounts/{accountid}/getaccessurl
	 * 
	 * Creates a temporary login link for authentication to a specific account. The lifetime of this login link is 2 minutes.
	 *
	 * Required parameters:
	 * $accountID - Account id
	 * 
	 * @param string $accountID
	 * @return apiResponse object
	 */
	public function getAccessUrl($accountID) {
		return $this->ApiRequest('/accounts/'.$accountID.'/getaccessurl');
	}
	
	/**
	 * POST accounts/{accountid}/changeexternalid/{externalid}
	 * 
	 * Changes the external id for an account. An 'external id' is an id of this account in your system.
	 *
	 * Required parameters:
	 * $accountID - Account id
	 * $externalid - External Id
	 * 
	 * @param string $accountID
	 * @param string $externalid
	 * @return apiResponse object
	 */
	public function changeExterternalId($accountID, $externalid) {
		return $this->ApiRequest('/accounts/'.$accountID.'/changeexternalid/'.$externalid, 'POST', new stdClass());
	}
	
	/**
	 * POST accounts/{accountid}/changeaccountstatus/{status}
	 * 
	 * Changes the status for the account
	 *
	 * Required parameters:
	 * $accountID - Account id
	 * $status - New status for the account. Possiable status values for the Basic account (sub-account): Active, Disabled. Possiable status values for the Premium account (multi-user account): Active, Suspended, Canceled.
	 *
	 * @param string $accountID
	 * @param string $status
	 * @return apiResponse object
	 */
	public function changeAccountStatus($accountID, $status) {
		return $this->ApiRequest('/accounts/'.$accountID.'/changeaccountstatus/'.$status, 'POST', new stdClass());
	}
	
	/**
	 * POST accounts/{accountid}/changeaccountplan/{accountplanid}
	 * 
	 * Changes (upgrades or downgrades) the plan of the account
	 *
	 * Required parameters:
	 * $accountID - Account id
	 * $accountplanid - New account plan id
	 * 
	 * @param string $accountID
	 * @param int $accountplanid
	 * @return apiResponse object
	 */
	public function changeAccountPlan($accountID, $accountplanid) {
		return $this->ApiRequest('/accounts/'.$accountID.'/changeaccountplan/'.$accountplanid, 'POST', new stdClass());
	}
	
	
	/**
	 * POST accounts/{accountid}/movebasicaccount/{destinationaccountid}
	 * 
	 * Moves a Basic account (sub-account) to another Premium account (multi-user account). The destination Premium account must exist in our platform.
	 *
	 * Required parameters:
	 * $accountID - Account id
	 * $destinationaccountid - Destination Premium account id
	 * 
	 * @param string $accountID
	 * @param string $destinationaccountid
	 * @return apiResponse object
	 */
	public function moveBasicAccount($accountID, $destinationaccountid) {
		return $this->ApiRequest('/accounts/'.$accountID.'/movebasicaccount/'.$destinationaccountid, 'POST', new stdClass());
	}
	
	/**
	 * DELETE accounts/{accountid}/deletebasicaccount
	 * 
	 * Deletes the Basic account
	 *
	 * Required parameters:
	 * $accountID - Account id
	 * 
	 * @param string $accountID
	 * @return apiResponse object
	 */
	public function deleteBasicAccount($accountID) {
		return $this->ApiRequest('/accounts/'.$accountID.'/deletebasicaccount/', 'DELETE', new stdClass());
	}
	
	/* 
	 * 	Site
	 *  API methods for working with sites (domains)
	 *  
	 */
	/**
	 * PUT sites
	 * 
	 * Creates a site on the Basic account (sub-account).
	 * 
	 * Required parameters:
	 * $AccountId - Site account id
	 * $SiteDomainName - Site partial sub-domain (include only the part before the '.', for example: if the full subdomain is abc.yourdomain.mobi, include 'abc')
	 *
	 * @param string $AccountId
	 * @param string $SiteDomainName
	 * @return apiResponse object
	 */
	public function createSiteOnBasicAccount($AccountId, $SiteDomainName) {
		$res = $this->ApiRequest('/sites', 'PUT', new Site(array('AccountId'=>$AccountId, 'SiteDomainName'=>$SiteDomainName)));
		if ($res->Success)	$res->Data = recast('Site', $res->Data);
		return $res;
	}
	
	/**
	 * GET sites/{siteid}
	 * 
	 * Get site information
	 * 
	 * Required parameters:
	 * $siteid - Site id 
	 *
	 * @param string siteid
	 * @return apiResponse object
	 */
	public function getSiteInformation($siteid) {
		$res = $this->ApiRequest('/sites/'.siteid);
		if ($res->Success) $res->Data = recast('Site', $res->Data);
		return $res;
	}
	
	/**
	 * DELETE sites/{siteid}
	 * 
	 * Delete the given site
	 *
	 * Required parameters:
	 * $siteid - Site id 
	 *
	 * @param string siteid
	 * @return apiResponse object
	 */
	public function deleteSite($siteid) {
		return $this->ApiRequest('/sites/'.$siteid, 'DELETE', new stdClass());
	}
	
	
	/* Template
	 * 
	 * 
	 * API methods for working with templates.
	 * 
	 */

	/**
	 * GET templates/containers/{containerid}/fields
	 * 
	 * Returns a list of data fields created for this container.
	 * 
	 * Required parameters:
	 * $$containerid - Data container id.
	 * 
	 * @param string $containerid
	 * @return apiResponse object
	 */
	public function getContainerFields($containerid) {
		$res =  $this->ApiRequest('/templates/containers/'.$containerid.'/fields');
		if ($res->Success) :
			if (is_array($res->Data)) :
				for ($i=0;$i<count($res->Data);$i++) :
						$res->Data[$i] = recast('ApiTemplateField', $res->Data[$i]);
				endfor;
			endif;
		endif;
		return $res;
	}

	/**
	 * GET templates/pagetemplates?accountid={accountid}
	 * 
	 * Returns a list of API page templates
	 * 
	 * Optional parameters:
	 * $accountID - Id of a Premium account. Include this parameter if you have the ability to create Premium (multi-user) accounts, otherwise leave blank.
	 * 
	 * @param string $accountID
	 * @return apiResponse object
	 */
	public function getPageTemplates($accountID='') {
		$res = $this->ApiRequest('/templates/pagetemplates/?accountid='.$accountID);
		if ($res->Success) :
			if (is_array($res->Data)) :
				for ($i=0;$i<count($res->Data);$i++) :
					$res->Data[$i] = recast('ApiPageTemplate', $res->Data[$i]);
				endfor;
			endif;
		endif;
		return $res;
	}
	
	/**
	 * GET templates/pagetemplates/getgeneratepagesstatus?jobid={jobid}
	 * 
	 * Returns the current status of a page generation task
	 * 
	 * Required parameters: 
	 * $jobid - Job Id of the page generation task
	 * 
	 * @param int $jobid
	 * @return apiResponse object
	 */
	public function getGeneratePagesStatus($jobid) {
		$res = $this->ApiRequest('/templates/pagetemplates/getgeneratepagesstatus?jobid='.$jobid);
		if ($res->Success) :
			$res->Data = recast('ApiGeneratePagesStatusResponse', $res->Data);
			if (is_object($res->Data->Data)) :
				$res->Data->Data = recast('ApiGeneratePagesData', $res->Data->Data);
			endif;
		endif;
		return $res;
	}
	
	/**
	 * POST templates/containers/{containerid}/generatepages
	 * 
	 * This method generates pages based on the data previously loaded into a data container.
	 * Notes : The relative URL of the page (/zzz) for each data item is a field you include when you load your data items. The full URL for that item's page is generated based on the siteId you provide for the data item (in the 'loaddataitems' method). Our platform looks up that site's domain by the siteId, and generates the full URL for the data item's page (e.g. 'mysite.mobi/zzz'). If another page already exists on this full URL, and the page was created with API templates for the same data item, the page will be updated.
	 * 
	 * Required parameters: 
	 * $containerid - Id of the API data container for generate pages.
	 * $items - array of item ids ('ItemId'=>N1, 'ItemId'=>N2, ...), or stdClass or ApiGeneratePagesRequest object 
	 * 
	 * @param int $containerid
	 * @param $items
	 * 
	 */
	public function generatePages($containerid, $items) {
		$res = $this->ApiRequest('/templates/containers/'.$containerid.'/generatepages', 'POST', new ApiGeneratePagesRequest($items));
		if ($res->Success) $res->Data = recast('ApiGeneratePagesResponse', $res->Data);
		return $res;
	}
	
	
	/**
	 * GET templates/containers?accountid={accountid}
	 * 
	 * Returns a list of data containers
	 * 
	 * Optional arguments: 
	 * $accountID - Premium account id. Include it if you have the ability to create Premium (multi-user) accounts.
	 * 
	 * @param string $accountID
	 * @return apiResponse object
	 * 
	 */
	public function getContainers($accountID='') {
		$res = $this->ApiRequest('/templates/containers?accountid='.$accountID);
		if ($res->Success) :
			if (is_array($res->Data)) :
				for ($i=0;$i<count($res->Data);$i++) :
					$res->Data[$i] = recast('ApiTemplateContainer', $res->Data[$i]);
				endfor;
			endif;
		endif;
		return $res;
	}
	
	
	/**
	 * POST templates/containers/{containerid}/loaddataitems
	 * 
	 * Validates and loads your data (data items) into a data container. Once the data is loaded, you can call the /GeneratePages/ method to generate the pages for these data items.
	 *
	 * Required arguments: 
	 * $containerid - Data container id.
	 * $items - List of data items to be loaded into this data container
	 * 
	 * @param int $containerid
	 * @param ApiTemplateLoadData $items
	 * @return apiResponse object
	 * 
	 */
	public function loadDataItems($containerid, $items) {
		$res = $this->ApiRequest('/templates/containers/'.$containerid.'/loaddataitems', 'POST', new ApiTemplateLoadData($items));
		if ($res->Success) :
			$res->Data = recast('ApiTemplateLoadDataResponse', $res->Data);
			if (is_array($res->Data->Items) && count($res->Data->Items)) :
				for ($i=0;$i<count($res->Data->Items);$i++) :
					$res->Data->Items[$i] = recast('ApiItemsForTemplateLoadDataResponse', $res->Data->Items[$i]);
				endfor;
			endif;
		endif;
		return $res;
	}
	
	/**
	 * GET templates/containers/{containerid}/getdataitems
	 * 
	 * This method returns all data items created within this data container.
	 * 
	 * Required arguments:
	 * $containerid - Data container id.
	 * 
	 * @param int $containerid
	 * @return apiResponse object
	 */
	public function getDataItems($containerid='') {
		$res = $this->ApiRequest('/templates/containers/'.$containerid.'/getdataitems');
		if ($res->Success) :
			if (is_array($res->Data)) :
				for ($i=0;$i<count($res->Data);$i++) :
					$res->Data[$i] = recast('ApiTemplateLoadDataItem', $res->Data[$i]);
					if (is_array($res->Data[$i]->Fields)) :
						for ($j=0;$j<count($res->Data[$i]->Fields);$j++) :
							$res->Data[$i]->Fields[$j] = recast('ApiTemplateFieldData', $res->Data[$i]->Fields[$j]);
						endfor;
					endif;
				endfor;
			endif;
		endif;
		return $res;
	}

	/*
	 * Location
	 * 
	 * API methods for working with location.
	 */	
	
	
	/**
	 * POST templates/locations/AddLocationSet?accountid={accountid}
	 * 
	 * Create location set
	 * 
	 * Required Parameters:
	 * $locationset
	 * 		$SiteId - Location set site id
	 * 		$Name   - Location set name
	 * 		$Id     - Location set Id (optional)
	 * 
	 * Optional Parameters:
	 * $accountID - Premium account id. Include it if you have the ability to create Premium (multi-user) accounts.
	 * 
	 * @param ApiLocationSet $locationset
	 * @param string $accountID
	 * @return apiResponse object
	 */
	public function addLocationSet($locationset, $accountID='') {
		$res = $this->ApiRequest('/templates/locations/AddLocationSet?accountid='.$accountID, 'POST', new ApiLocationSet($locationset));
		if ($res->Success) $res->Data = recast('ApiLocationSet', $res->Data);
		return $res;
	}
	
	/**
	 * GET templates/locations/GetLocationSets?accountid={accountid}
	 * 
	 * Get location sets
	 * 
	 * Optional parameters:
	 * $accountID - Premium account id. Include it if you have the ability to create Premium (multi-user) accounts.
	 * 
	 * @param string $accountID
	 * @return apiResponse object
	 */
	public function getLocationSets($accountID='') {
		$res = $this->ApiRequest('/templates/locations/GetLocationSets?accountid='.$accountID);
		if ($res->Success) :
			if (is_array($res->Data)) :
				for ($i=0;$i<count($res->Data);$i++) :
					$res->Data[$i] = recast('ApiLocationSet', $res->Data[$i]);
				endfor;
			endif;
		endif;
		return $res;
	}

	/**
	 * POST templates/locations/DeleteLocationSet?locationSetId={locationSetId}&accountid={accountid}
	 * 
	 * Delete location set
	 * 
	 * Required parameters:
	 * $locationSetId  - Location set id
	 * 
	 * Optional Parameters:
	 * $accountID - Premium account id. Include it if you have the ability to create Premium (multi-user) accounts.
	 * 
	 * @param int $locationSetId
	 * @param string $accountID
	 * @return apiResponse object
	 * 
	 */
	public function deleteLocationSet($locationSetId, $accountID='') {
		return $this->ApiRequest('templates/locations/DeleteLocationSet?locationSetId='.$locationSetId.'&accountid='.$accountID, 'POST', new stdClass());
	}
	
	 /**
	 * POST templates/locations/UpdateLocationSet?accountid={accountid}
	 * 
	 * Update location set
	 * 
	 * Required Parameters:
	 * $locationset - Location set object
	 * 
	 * Optional Parameters:
	 * $accountID - Premium account id. Include it if you have the ability to create Premium (multi-user) accounts.
	 * 
	 * @param ApiLocationSet $locationset
	 * @param string $accountID
	 * @return apiResponse object
	 */
	public function updateLocationSet($locationset, $accountID='') {
		$res = $this->ApiRequest('/templates/locations/UpdateLocationSet?accountid='.$accountID, 'POST', new ApiLocationSet($locationset));
		if ($res->Success) $res->Data = recast('ApiLocationSet', $res->Data);
		return $res;
	}
	
	/**
	 * POST templates/locations/AddLocationItem?locationSetId={locationSetId}&accountid={accountid}
	 * 
	 * Create location item for location set
	 * 
	 * Required parameters:
	 * $locationSetId - Location set id
	 * $locationsetitem - ApiLocationSetItem object
	 * 
	 * Optional Paramters:
	 * $accountID - Premium account id. Include it if you have the ability to create Premium (multi-user) accounts.
	 * 
	 * @param int $locationSetId
	 * @param ApiLocationSetItem $locationsetitem
	 * @param string $accountID
	 * @return apiResponse object
	 */
	public function addLocationItem($locationsetitem, $locationSetId, $accountID='') {
		$res = $this->ApiRequest('/templates/locations/AddLocationItem?locationSetId='.$locationSetId.'&accountid='.$accountID, 'POST', new ApiLocationSetItem($locationsetitem));
		if ($res->Success) $res->Data = recast('ApiLocationSetItem', $res->Data);
		return $res;
	}
	
	/**
	 * GET templates/locations/GetLocationItem?itemId={itemId}&accountid={accountid}
	 * 
	 * Get location item
	 * 
	 * Required parameters:
	 * $itemId - Location item id
	 * 
	 * Optional parameters:
	 * $accountID - Premium account id. Include it if you have the ability to create Premium (multi-user) accounts.
	 * 
	 * @param int $itemId
	 * @param string $accountID
	 * @return apiResponse object
	 */
	public function getLocationItem($itemId, $accountID) {
		$res = $this->ApiRequest('/templates/locations/GetLocationItem?itemId='.$itemId.'&accountid='.$accountID);
		if ($res->Success) $res->Data = recast('ApiLocationSetItem', $res->Data);
		return $res;
	}
	
	/**
	 * POST templates/locations/UpdateLocationItem?accountid={accountid}
	 *
	 * Update location item
	 *
	 * Required Parameters:
	 * $locationsetitem - Location set Item object
	 *
	 * Optional Parameters:
	 * $accountID - Premium account id. Include it if you have the ability to create Premium (multi-user) accounts.
	 *
	 * @param ApiLocationSetItem $locationsetitem
	 * @param string $accountID
	 * @return apiResponse object
	 */
	public function updateLocationItem($locationsetitem, $accountID='') {
		$res = $this->ApiRequest('/templates/locations/UpdateLocationItem?accountid='.$accountID, 'POST', new ApiLocationSetItem($locationsetitem));
		if ($res->Success) $res->Data = recast('ApiLocationSetItem', $res->Data);
		return $res;
	}
	
	/**
	 * POST templates/locations/DeleteLocationItem?itemId={itemId}&accountid={accountid}
	 * 
	 * Delete location item
	 * 
	 * Require parameters:
	 * $itemId - Location item id
	 * 
	 * Optional paramters:
	 * $accountID - Premium account id. Include it if you have the ability to create Premium (multi-user) accounts.
	 * 
	 * @param int $itemId
	 * @param string $accountID
	 * @return apiResponse object
	 */
	public function deleteLocationItem($itemId, $accountID='') {
		return $this->ApiRequest('/templates/locations/DeleteLocationItem?itemId='.$itemId.'&accountid='.$accountID, 'POST', new stdClass());
	}
	
	
	/**
	 * Class constructor
	 *
	 * Required arguments:
	 * key -  API Key
	 *
	 * Optional parameters:
	 *  customLoadTokenCallback - function to get previously saved temporary access token
	 *  customSaveTokenCallback - function to save temporary access token
	 *
	 * @param string $key
	 * @param void $customLoadTokenCallback
	 * @param void $customSaveTokenCallback
	 */
	function __construct($key, &$customLoadTokenCallback = null, &$customSaveTokenCallback = null) {
		if (strlen($key)>0)	{
			$this->apiKey = $key;
		}
		//filename to store temporary access token
		$this->tmpTokenFilename = sys_get_temp_dir().'/resapi.txt';
		
		//set access token load/save callbacks
		$this->loadTokenCallback = ( (isset($customLoadTokenCallback) && is_callable($customLoadTokenCallback)) ? $customLoadTokenCallback : array(&$this, 'loadTokenFromFile') );
		$this->saveTokenCallback = ( (isset($customSaveTokenCallback) && is_callable($customSaveTokenCallback)) ? $customSaveTokenCallback : array(&$this, 'saveTokenToFile') );
		$this->getAccessToken(); //load stored access token
	}

	/**
	 * Get "Temporary Access Token"
	 *
	 * @return string TOKEN
	 */
	public function getAccessToken() {
		$token = call_user_func($this->loadTokenCallback);
		if (strlen($token)>0) {
			$this->accessToken = $token;
		}
	}
	/**
	 * Save "Temporary Access Token" (after call "Authentication")
	 *
	 * @param string $newToken
	 */
	public function setAccessToken($newToken) {
		if (strlen($newToken)>0) {
			$this->accessToken = $newToken;
			@call_user_func($this->saveTokenCallback, $newToken);
		}
	}

	/**
	 * Authentication
	 *
	 * Authentication method gets the temporary token for API access. Lifetime is 240 minutes.
	 *
	 *
	 * @return none
	 */
	public function Authentication() {
		$res =  $this->_doApiRequest('/authentication/getapitoken?key='.$this->apiKey);
		if ($res->Success) {
			$this->setAccessToken($res->Data->Token);
		}
		return $res;
	}

	
	

	/**
	 * Make the API request
	 *
	 * required $url - API URL
	 * required $method - API METHOD (GET/POST/DELETE/...etc...)
	 * optional $postBody: xml-string (for POST requests)
	 *
	 * returns the $res array
	 * possible $res array keys:
	 * 		success: true/false
	 * 		result:  object
	 * 		raw_result: raw response from server
	 * 		status: HTTP-response code from server
	 * 		errors: array of strings. Each string contain some error description
	 *
	 *
	 * @param string $url
	 * @param string $method
	 * @param any $postBody
	 * @return array $res
	 */
	protected function apiRequest($url, $method='GET', $postBody='') {

		$res = $this->_doApiRequest($url, $method, $postBody);

		if ((!$res->Success) ) {
			$res = $this->Authentication();
			if ($res->Success) {
				$res = $this->_doApiRequest($url, $method, $postBody);
			}
		}
		return $res;
	}

	protected function _doApiRequest($url, $method='GET', $postBody='') {
		$res = new apiResponse();
		$jsonPostBody = json_encode($postBody);

		$ch = curl_init();
		if ($method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPostBody);
		} else if ($method == 'DELETE') {
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		}
		else if ($method == 'PUT') {
			curl_setopt($ch, CURLOPT_PUT, true);

			$fp = fopen('php://temp/maxmemory:256000', 'w');
			if (!$fp) {
				die('could not open temp memory data');
			}
			fwrite($fp, $jsonPostBody);
			fseek($fp, 0);
				
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			curl_setopt($ch, CURLOPT_INFILE, $fp); // file pointer
			curl_setopt($ch, CURLOPT_INFILESIZE, strlen($jsonPostBody));
				
		}
		curl_setopt($ch, CURLOPT_URL, $this->apiURL.$url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type:text/json',
		'Authentication: '.$this->accessToken
		//'Content-Length: ' .strlen($postBody)
		)
		);

		if (defined ('API_DEBUG') && API_DEBUG) {
			curl_setopt($ch, CURLOPT_VERBOSE, 1);
		}

		curl_setopt($ch, CURLOPT_FAILONERROR, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 100); // times out after 10s

		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		$result = curl_exec($ch); // run the whole process

		if (empty($result)) 	{
			// some kind of an error happened
			curl_close($ch); // close cURL handler
			$res->Messages[] = new ErrorMessage(9997, curl_error($ch));
			
		} else {
			$info = curl_getinfo($ch);
			curl_close($ch); // close cURL handler
			if (empty($info['http_code'])) {
				$res->Messages[] = new ErrorMessage(9999, 'No HTTP code was returned');
			} else if ($info['http_code'] !== 200) {
				
				ob_start(); var_dump($result); $str = ob_get_clean();
				$res->Messages[] = new ErrorMessage(9998, 'Server returned bad status code: '.$info['http_code'], array('Server returned data:', $str));
				//$res['result'] = json_decode($result);
			} else {
				$tmp = json_decode($result);
				$res->Success = (is_object($tmp) && $tmp->Success) ? true : false;
				$res->Data = $tmp->Data;
				if ($tmp->Messages) $res->Messages = $tmp->Messages;
			}
		}
		return recast('apiResponse', $res);
	}

	private function loadTokenFromFile() {
		return trim(@file_get_contents($this->tmpTokenFilename));
	}

	private function saveTokenToFile($token) {
		file_put_contents($this->tmpTokenFilename, $token, LOCK_EX);
	}

}
class apiStdClass extends stdClass {
	public function __construct($data=null) {
		if (is_array($data)) {
			foreach ($data as $key => $val) :
			$this->$key = $val;
			endforeach;
		} else if (is_object($data)) {
			foreach (get_object_vars($data) as $key => $val) :
			$this->$key = $val;
			endforeach;
		}
	}
}
class PageListOfAccount extends apiStdClass {
	public $Items = array();
	public $AllCount = 0;
}
class apiResponse extends apiStdClass {
	public $Data = null;
	public $Success = false;
	public $Messages = null;
}
class ApiLPMember extends apiStdClass {
	public $Id = null;
	public $FirstName = '';
	public $LastName = '';
	public $Phone = '';
	public $Email = '';
	public $Birthday = '';
	public $Notes = '';
	public $StatusCode = '';
	public $ExternalAccountId = '';
}
class PageListOfApiLPMember extends apiStdClass {
	public $Items = array();
	public $AllCount = 0;
}
class PageListOfApiLPEmployee extends apiStdClass {
	public $Items = array();
	public $AllCount = 0;
}
class ApiLPEmployee extends apiStdClass {
	public $Id = null;
	public $FirstName = '';
	public $LastName = '';
	public $Phone = '';
	public $Email = '';
	public $Birthday = '';
	public $Notes = '';
	public $StatusCode = '';
	public $StatusDescription = '';
	public $ExternalAccountId = '';
}
class ApiLPActionInfo extends apiStdClass {
	public $Id = null;
	public $LoyaltyProgramId = null;
	public $Name = '';
	public $Description = '';
	public $Points = 0;
	public $StatusCode = '';
	public $StatusDescription = '';
}
class ApiLPRewardInfo extends apiStdClass {
	public $Id = null;
	public $LoyaltyProgramId = null;
	public $Name = '';
	public $Description = '';
	public $Expired = '';
	public $Points = 0;
	public $RedeemCount = 0;
	public $StatusCode = '';
	public $StatusDescription = '';
}
class ApiLPTransaction extends apiStdClass {
	public $MemberId = null;
	public $EmployeeId = null;
	public $Created = '';
	public $Points = 0;
	public $Amount = 0;
	public $StoreNumber = '';
}
class ApiLPActionTransaction extends apiStdClass {
	public $ActionId = null;
	public $EmployeeId = null;
	public $MemberId = null;
}
class ErrorMessage extends apiStdClass {
	public $Error = '';
	public $Code = null;
	public $Details = array();
	function __construct($customCode=null, $err=null, $det=null){
		if ($customCode) $this->Code = $customCode;
		if ($err) $this->Error = $err;
		if ($det) $this->Details = $det;
	}
}
class ApiLPRedemptionTransaction extends apiStdClass {
	public $RewardId = null;
	public $MemberId = null;
	public $EmployeeId = null;
	public $Created = null;
	public $Points = 0;
	public $Amount = 0;
	public $StoreNumber = 0;
}
class PremiumAccountInfo extends apiStdClass {
	public $CompanyShortName = '';
	public $CompanyFullName = '';
	public $MobileDomain = '';
	public $ControlPanelDomains = array();
	public $SupportEmail = '';
	public $NoreplyEmail = '';
}
class Account extends apiStdClass {
	public $Id = null;
	public $ExternalId = null;
	public $FirstName = '';
	public $LastName = '';
	public $Email = '';
	public $Password = '';
	public $Type = '';
	public $AccountPlanId = 0;
	public $Status = '';
	public $ParentAccountId = 0;
	public $SubAccounts = array();
	public $Sites = array();
	public $PremiumAccountInfo  = null;
	public $IframeStatsToken = '';
}
class Site extends apiStdClass {
	public $AccountId = null;
	public $Id = null;
	public $SiteDomainName = '';
	public $SiteFullDomainName = '';
	public $ExternalDomainNames = array();
	public $Created = '';
}
class AccountPlan extends apiStdClass {
	public $Id = null;
	public $Type = '';
	public $Name = '';
	public $Description = '';
	public $PremiumAccountPlanInfo = null;
	public $BasicAccountPlanInfo = null;
}
class PremiumAccountPlanInfo extends apiStdClass {
	public $SitesLimit = 0;
	public $AdminsLimit = 0;
	public $SubAccountsLimit = 0;
}
class BasicAccountPlanInfo extends apiStdClass {
	public $PagesLimit = 0;
	public $SitesLimit = 0;
}

class ApiTemplateField extends apiStdClass {
	public $Id = null;
	public $Name = '';
	public $Type = '';
	public $PossibleValues = array();
}

class ApiPageTemplate extends apiStdClass {
	public $Name = '';
	public $Id = null;
	public $ContainerId = null;
}
class ApiGeneratePagesStatusResponse extends apiStdClass {
	public $Status = '';
	public $Data = null;
}
class ApiGeneratePagesData extends apiStdClass {
	public $PageRegenerated = null;
	public $Errors = array();
	public $Warnings = array();
}
class ApiGeneratePagesResponse extends apiStdClass {
	public $JobId = null;
}
class ApiTemplateContainer extends apiStdClass {
	public $Id = null;
	public $Name = '';
}
class ApiTemplateLoadData extends apiStdClass {
	public $Items = array();
}
class ApiTemplateLoadDataItem extends apiStdClass {
	public $Id = null;
	public $Name = '';
	public $DomainId = null;
	public $PageTemplateId = null;
	public $Fields = array();
}
class ApiTemplateFieldData extends apiStdClass {
	public $FieldName = '';
	public $Value = '';
}
class ApiTemplateLoadDataResponse extends apiStdClass {
	public $ProcessedItemCount = null;
	public $SuccessProcessedItemCount = null;
	public $ProcessedWithErrorItemCount = null;
	public $Items = array();
	public $Errors = array();
}
class ApiItemsForTemplateLoadDataResponse extends apiStdClass {
	public $Id = null;
	public $Name = '';
}
class ApiGeneratePagesRequest extends apiStdClass {
	public $ItemId = null;
}
class ApiLocationSet extends apiStdClass {
	public $Name = null;
	public $Id = null;
	public $SiteId = null;
}
class ApiLocationSetItem extends apiStdClass {
	public $Id = null;
	public $Address = null;
	public $City = null;
	public $Zip = null;
	public $State = null;
	public $Country = null;
	public $Phone = null;
	public $Name = null;
	public $Description = null;
	public $Latitude = null;
	public $Longitude = null;
	public $StoreNumber = null;
}

function recast($className, stdClass &$object)
{
	if (!class_exists($className))
		throw new InvalidArgumentException(sprintf('Inexistant class %s.', $className));

	$new = new $className();

	foreach($object as $property => &$value)
	{
		$new->$property = &$value;
		unset($object->$property);
	}
	unset($value);
	$object = (unset) $object;
	return $new;
}


?>