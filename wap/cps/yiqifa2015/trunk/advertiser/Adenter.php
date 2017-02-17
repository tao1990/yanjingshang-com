<?php
	/**
	 * ��¼���𷢲�����
	 * 
	 * ==============================================================================================================================================
	 * ˵����
	 * 	������Ҫ�����ǽ����������ݹ����Ĳ���д��cookie�У��������Ϣ����src(������Դ)��channel(ҵ�������ʽ)��cid(���𷢷���Ļid)��wi(����վ��ı�ʶ)��target_url(Ŀ���ַ);cookie�����Ѿ��������ļ�"yiqifa-config.php"�����ã�ͨ����������union.cookie.name���ɻ��cookie��
	 *
	 * ==============================================================================================================================================
	 * @auther lsj
	 * @see CallAdenter.php
	 * @see util.Config
	 * @version 0.2
	 * 
	 */
	class Adenter{		 
		private $DEFAULT_CAMPAIGN_ID = "101";					// �id ��cid������ʱ��Ҫ���������ṩ��cid
		private $DEFAULT_TARGET 	 = "http://www.baidu.com"; 	 // ��½ҳ�棬Ĭ������ҳ
		private $DEFAULT_CHANNEL     = "cps";					// ҵ�������ʽ
		public 	$UNION_COOKIE_NAME 	 = "yiqifa";				// �����cookie��
		private $UNION_COOKIE_DOMAIN = ".baidu.com"; 			// �滻Ϊ�Լ���վ����
		private $UNION_COOKIE_MAXAGE = 2592000;					// cookie��Ч�ڣ�Ĭ����30��
		private $CLEAN_COOKIE_NAMES  = ",sina,linkt,chengguo,";// ����ж����Դ�����ݣ���cookie���ֲ���ͬ����д������

		function Adenter(){
			$config = new Config();		
			//��Ҫ������ʼ
			$cid = $config -> getString("default_campaign_id");
			if (!empty($cid)) {
				$this -> DEFAULT_CAMPAIGN_ID = $cid;
			}
			
			$target = $config -> getString("default_target");
			if (!empty($target)) {
				$this -> DEFAULT_TARGET = $target;
			}
			
			$cdomain = $config -> getString("union_cookie_domain");
			if (!empty($cdomain)) {
				$this -> UNION_COOKIE_DOMAIN = $cdomain;
			}
			
			$channel = $config -> getString("default_channel");
			if (!empty($channel)) {
				$this -> DEFAULT_CHANNEL = $channel;
			}
			
			$cname = $config -> getString("union_cookie_name");
			if (!empty($cname)) {
				$this -> UNION_COOKIE_NAME = $cname;
			}
			
			$cage = $config -> getString("union_cookie_maxage");
			if (!empty($cage)) {
				$this -> UNION_COOKIE_MAXAGE = $cage;
			}
			
			$cnames = $config -> getString("clean_cookie_names");
			if (!empty($cnames)) {
				$this -> CLEAN_COOKIE_NAMES = $cnames;
			}
		}
		
		
		/**
		 * <code>jump($source,$channel,$campagin_id,$yiqifa_wi,$target_url)</code>�������ڽ��ղ����������������������ֵд��cookie�������ת��ָ����Ŀ���ַ��
		 * 
		 * ��������ļ��б�Ҫ�����Ƿ�������ȷ:
		 * 
		 * ����ִ��ʱ���� cookie������Ĭ��Ŀ���ַ��Ĭ�ϻid���������δ���û��ǿմ�����Ĭ�ϻid�����������׳�{@code Exception}�쳣
		 * ����������:
		 * 
		 * ��������е�Ŀ���ַ(target_url)���ƹ�����Ϊnull��մ����滻Ϊ�����ļ���Ĭ��ֵ;
		 * �����ԴΪnull��մ��򲻼�¼cookieֱ����ת��Ŀ���ַ;
		 * ���refererΪ�����¼��־��Ϣ���������պ��ѯ����������ƹ�;
		 * ��������еĻid�����������滻Ϊ�����ļ���Ĭ��ֵ;
		 * ��������е�վ��idΪ�����¼��־��
		 * ����������������cookie:
		 * 
		 * ��������ļ���������Ҫ����������������cookie������������Щcookie����ֹͬһ������ͬʱ���͸����������顣
		 * д�������ƹ��õ�cookie:
		 * 
		 * ����������е�������Դ���ƹ��������id��վ���Ŀ���ַ��ֵд��cookie�У���":"�ָ����:"emar:cps:NDgwMDB8dGVzdA==:http://www.XXX.com"��
		 * 
		 * 
		 * @param source
		 * @param channel
		 * @param campagin_id
		 * @param yiqifa_wi
		 * @param target_url
		 * @throws new Exception �id�� Ĭ��Ŀ���ַ�� cookie���������ô���ʱ�׳����쳣��
		 */
		function jump($source,$channel,$campagin_id,$yiqifa_wi,$target_url){	
			
			if (empty($this-> UNION_COOKIE_DOMAIN)) {	// �������Ϊ��,���׳��쳣
				throw new Exception("Cookie domain is null!",136);
			}

			if (empty($this -> DEFAULT_CAMPAIGN_ID) || !is_numeric($this->DEFAULT_CAMPAIGN_ID)) {// ����idΪ��,���׳��쳣
				throw new Exception();
			}
			if (empty($this -> DEFAULT_TARGET)) {		// ���Ŀ���ַΪ��,���׳��쳣
				throw new Exception();
			}

			/* =============================����У��============================== */
			if (empty($target_url)) {					// ���Ŀ���ַΪ�գ�����ΪĬ��Ŀ���ַ
				$target_url = $this-> DEFAULT_TARGET;
			} else if (!strcmp(substr($target_url, 0,6),"http://")
					&& !strcmp(substr($target_url, 0,7),"https://")) { // ���Ŀ���ַ�����ԡ�http://����https://����ͷ�������"http://"
				$target_url = "http://" + $target_url;
			}
		   if(stripos($target_url,$this -> UNION_COOKIE_DOMAIN)===false){ //���Ŀ���ַΪ���������ĵ�ַ���滻��Ĭ�ϵ���תҳ��
				$target_url = $this-> DEFAULT_TARGET;
			}

			if (empty($source)) { 							// �������Դ������¼Cookieֱ����ת��targetָ���ĵ�ַ
				header('Location: '.$target_url.'/');
				return;
			}
			
			//��������
			$http_host  = $_SERVER["HTTP_HOST"];
			//����ĵ�ַ
			$requestUrl = $_SERVER["REQUEST_URI"] ;
			//��ȡ��ַ����Ĳ���
			$visitParam = $_SERVER["QUERY_STRING"];
			//��ȡ��ϸ�������ַ
			$visitReferer = $_SERVER["HTTP_REFERER"];	
			$visit = "";
			if (!empty($visitParam)) { 				// ƴװ��ϸ�������ַ
				$visit = $http_host.$requestUrl;
			}		

			if (empty($visitReferer)){
				$visitReferer = "";
			}
			if (empty($channel)) {
				$channel = $this ->  DEFAULT_CHANNEL;
			}
	
			if (!is_numeric($campagin_id)) { // ����IDȱʧ�������֣����滻ΪĬ�ϵĻID����ȷ�ĻID�������ṩ
				$cid = $this ->  DEFAULT_CAMPAIGN_ID;
			}
			if (empty($yiqifa_wi)) { // ���վ���ʶΪ�գ��׳��쳣
				throw new Exception("wi is null!");
			}
	
			// �鿴�Ƿ����������˵�cookie��������������ֹ���������˽���
			if ($this -> CLEAN_COOKIE_NAMES != null) {
				$list = explode(",",$this -> CLEAN_COOKIE_NAMES);
				foreach ($list as $value){
					 if(strlen($_COOKIE[$value]) > 0){					 			
					 	setcookie($value,'',time() - $this -> UNION_COOKIE_MAXAGE,'/');
					 }
				}
			}

	
			// д��������Դ��cookie
			$yiqifa_wi  = empty($yiqifa_wi) ? "" : $yiqifa_wi;
			$cookieValue = $source . ":" . $channel . ":" . $campagin_id . ":" . $yiqifa_wi ;
			$cookieValue = urlencode($cookieValue);	

			
			setcookie($this->UNION_COOKIE_NAME,$cookieValue,time() + $this->UNION_COOKIE_MAXAGE,"/",$this -> UNION_COOKIE_DOMAIN);
			header("Location: ".$target_url);
		}
	}
?>