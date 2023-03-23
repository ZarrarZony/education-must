<?php

function create_brand_list(){
	return $list = array('F','L','P','T','U','V','K','I','CW','KX','NX');
}

function get_brand($referral){
	if(get_option('default_crm_refr') == $referral){
		return get_option('default_crm_brand');
	}
	elseif(!empty(get_option('usr_refr_brand_list')) && array_key_exists($referral,get_option('usr_refr_brand_list')))
	{
		return get_option('usr_refr_brand_list')[$referral];
	}
	else{
		return 'not found';
	}
}

function get_web_url(){
	preg_match('/^(?:https?:\/\/)?(?:www\.)?([\w\-.]+)/', get_home_url(), $matches);
	return $matches[1];
}