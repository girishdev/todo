<?php namespace App\Http\Controllers\Client;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Request;
use App\ClientModels\Internal\CepClientTemplateConfigModel;
use App\ClientModels\Internal\CepClientArticleModel;
use App\CepProductConfigurations;
use App\CepProducts;
use App\CepItems;
use App\CepHashList;
use App\CepDownloads;
use App\CepUploads;

use DB;
use File;
use Validator;
use Input;
use Auth;
use Crypt;

/* Services */
use App\Services\UploadsManager;

/* Libraries */
use App\Libraries\FileManager;
use App\Libraries\CheckAccessLib;
use App\Libraries\ProductHelper;
use App\Libraries\EMailer;
use App\Libraries\ActivityMainLib;
use App\Libraries\ExcelLib;
use App\Libraries\WordLib;
use App\Libraries\WordReader;
use App\Libraries\BasicLib;
use App\Libraries\EncDecSplCharLib;
use App\Libraries\ZipLibrary;

class InternalTemplateController extends Controller {
	/**
     * Instantiate a new UserController instance.
     */
    public function __construct(\Illuminate\Http\Request $request)
    {
    	$this->middleware('auth');

    	$this->permit=$request->attributes->get('permit');
    	$this->configs=$request->attributes->get('configs');
    	$this->dictionary=$request->attributes->get('dictionary');

    	$this->manager=new UploadsManager;
    	$this->activityObject = new ActivityMainLib;
    	$this->checkaccess = new CheckAccessLib;
    	$this->productHelper = new ProductHelper;

    	$this->emailActivity = new EMailer;
        $this->templatesactivity = new ActivityMainLib;

        $this->excellibobj = new ExcelLib;
        $this->word = new WordLib;
        $this->FileManager=new FileManager();
        $this->model=new CepClientArticleModel();
        $this->edsLib=new EncDecSplCharLib();
        $this->zipLib=new ZipLibrary();

    }
	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$configurations = CepClientTemplateConfigModel::all();
		return view('clients_custom.epinternal.index',compact('configurations'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		// return 'This is in Create..';
		$configuration = new CepClientTemplateConfigModel();
		return view('clients_custom.epinternal.create',compact('configuration'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$data = \Input::all();
		$config = CepClientTemplateConfigModel::find($data['id']);
		if(is_null($config))
		{
			$config = new CepClientTemplateConfigModel();
		}
		$config->name = $data['name'];
		$config->description = $data['description'];
		$config->column_count = $data['column_count'];
		$config->xlsx_headers = json_encode(explode(",",$data['colHeaders']));

		if(isset($data['static_data']))
		{
			$config->static_data = json_encode($data['static_data']);
		}
		if(isset($data['choose_header']))
		{
			array_unshift($data['choose_header'],"");
			$config->choose_header = json_encode($data['choose_header']);
		}
		if(isset($data['filename']))
		{
			array_unshift($data['choose_header'],"");
			$config->filename = json_encode($data['filename']);
		}
		if(isset($data['delivery_data2']))
		{
			array_unshift($data['delivery_data2'],"");
			$config->delivery_data = json_encode($data['delivery_data2']);
		}
		if(isset($data['dynamic_data']))
		{
			$new_array = array();

			$new_array = array_merge($data['pre'],$data['dynamic_data']);
			$new_array = array_merge($new_array,$data['post']);
			//dd($data['option']);

			if(isset($data['common_rules']))
			{
				if(empty($data['common_rules']))
				{
					$data['common_rules'] = array();
				}
			}
			else
			{
				$data['common_rules'] = array();
			}
			for($i = 0; $i < count($data['post']); $i++)
			{
				if(!isset($data['option'][$i]) || $data['option'][$i] == '--Select--')
				{
					$data['option'][$i] = $data['common_rules'];
				}
				else
				{
					$data['option'][$i] = array_merge($data['option'][$i],$data['common_rules']);
				}
			}
			ksort($data['option']);
			$new_array = array_merge($new_array,$data['option']);
			//dd(json_encode($new_array));
			$config->dynamic_data = json_encode($new_array);
		}
		if(isset($data['extra_data']))

		{
			$config->extra_data = json_encode($data['extra_data']);
		}
		else
		{
			$config->extra_data = json_encode(array());
		}

		if(isset($data['imp1_data']) && $data['imp1_data'] != '')
		{
			$config->imp1_data = trim($data['imp1_data']);
		}
		if(isset($data['imp2_data']) && $data['imp2_data'] != '')
		{
			$config->imp2_data = trim($data['imp2_data']);
		}
		if(isset($data['imp3_data']) && $data['imp3_data'] != '')
		{
			$config->imp3_data = $data['imp3_data'];
		}
		$config->generic_rules = json_encode($data['common_rules']);

		$config->save();

		return redirect('client/ep-internal/configuration')->with('success','Configuration saved!');
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$configuration = CepClientTemplateConfigModel::find($id);
		$encdec = new EncDecSplCharLib();
		foreach(json_decode($configuration->xlsx_headers) as $header)
		{
			$headers[] = $encdec->unicodeToAscii($header);
		}
		$configuration->xlsx_headers = $headers;
		return view('clients_custom.epinternal.create',compact('configuration'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}

	public function convertToDoc()
	{
		// return 'First view Import...';
		$configurations = CepClientTemplateConfigModel::lists('name','id');
		$products = CepProducts::lists('prod_name','prod_id');
		//dd($products);
		return view('clients_custom.epinternal.import',compact('configurations','products'));
	}//End of convertToDoc

	public function convertToDocProcess()
	{	
		// return 'Second view Import Process...';
		if(Request::isMethod('get'))
        	return redirect()->back()->with('customError', $this->dictionary->msg_templates_process_fail);

		$data = Request::all();
		$item = CepItems::where('item_product_id',$data['products'])->first();
		//dd($data['products']);
		/* Validate */
		$validate = Validator::make($data,[
                 		'file' => 'required'
                 	]);
        if($validate->fails())
        {
			return redirect()->back()->withErrors($validate->errors());
        }

		$filepath = "";
		$itemData = CepItems::leftjoin('cep_product_users','item_product_id','=','puser_product_id')
							->leftjoin('users','puser_user_id','=','id')
							->leftjoin('cep_groups','cep_groups.group_id','=','users.group_id')
							->where('item_id',$item->item_id)
							->where('group_code','CL')
							->select('id','item_product_id','item_id')
							->first();


        $file=Input::file('file');
        //print_r($file);
		$upload=array();
		if($file)
		{
	        $name=uniqid();
			$options =array (
		 				'name'=>$name,
		 				'type'=>'templates',
						'description'=> 'custom upload',
						'url'=> '/products/'.$itemData->item_product_id.'/templates/inputs/',
						'client'=> $itemData->id,
						'product'=> $itemData->item_product_id,
						'item'=> $itemData->item_id,
						'reference_column'=> '',
          				'status'=>  1
						);

			$upload=$this->FileManager->upload($file,$options);

			if(!is_null($upload))
			{
				$productHelper=new ProductHelper;
				$prod=$productHelper->checkProductExists($itemData->item_product_id);
				$prodUsers=$productHelper->getClientProductUsers($itemData->item_product_id,array('BO'));

				/* Activity Alerts */
				//echo "<pre>"; print_r($prodUsers);exit;
				$variable = '{ "EM_PRODUCT ": "'.$prod->name.'"}';

				/* Activity Custom */

		        $variable_act = '{ "AC_PRODUCT": "'.$prod->name .'"}';
				$act_options = array(
		                            'act_template_id' => $this->configs->template_creation_activity_template_id,
		                            'act_by' => Auth::id(),
		                            'act_product_id' => $itemData->item_product_id,
		                            'act_variables' => $variable_act
		                           );
		        $this->templatesactivity->postActivity($act_options);

                /* Close Activity Custom */
				/* Close Activity Email Alerts */
                return $writerFile=$this->createTemplates($upload,$data['products'],$data['configuration']);
			}


		}

      	//if($upload_status['iconf_value'] == 0)
       // return redirect()->back();
        $redirect="product/writer/".$itemData->item_product_id;
        //echo $redirect;
		//return array($redirect,'success','');\
		return redirect($redirect)->with('success', array('Teamplates  Generated Successfully '));
	}

	/**
	 * Function createTemplates
	 *
	 * @param
	 * @return
	 */
	public function createTemplates($upload,$product,$configuration_id)
	{
		$basicLibr = new BasicLib();
		$configuration = CepClientTemplateConfigModel::find($configuration_id);
		$this->prodConfigs=$this->productHelper->getProductDevConfigs($product);
		//$type=$this->prodConfigs['pdc_client_article_type'];
		$type= 0;
		$file=public_path()."/uploads/".$upload->upload_url;

		$fileData=$this->excellibobj->readExcel($file);
		$fileColors=$this->excellibobj->readExcelColors($file);
		$prefixWriter= '';
		//echo "<pre>"; print_r($fileData);exit;

		/* Create New folder for templates  */
		$folder=$prefixWriter.$configuration->name."_Template-".uniqid()."-".rand(1111,9999);
		$fileStoragePath=public_path().'/uploads/products/'.$product.'/templates/'.$folder;

		File::makeDirectory($fileStoragePath, $mode = 0777, true, true);
		$templatesArrray=array();
		$xlsxArray=array();
		$updateIds=array();

		/* Based on these keys we segregate data which to fill and which not to fill  */
		$staticKeys=json_decode($configuration->static_data); //array(0,1,2,3,4,5,6,7,8);
		$dynamicKeys=json_decode($configuration->dynamic_data);//($type==0)?array(9,10,11,12):array();
		// Remove pre - post information
		$dynamicKeys = array_intersect_key($dynamicKeys, array_filter($dynamicKeys, 'is_numeric'));

		$extraKeys=array();
		$header=array();
		$headerColor=array();
		$header2=array();
		$header2Color=array();

		foreach ($fileData as $key => $value) {
			$articleId='';
			if(array_filter($value)){
				if($key==1)
				{
					$header=$value;
					$headerColor=$fileColors[$key];
				}
				if($key==2)
				{
					$header2=$value;
					$header2Color=$fileColors[$key];
					array_unshift($value,"URL");
					$xlsxArray[]=$value;
				}
				if($key>2)
				{
					/* Get Article id Auto incremented value from DB */

					$articleId=$this->model->nextAutoIncrementId();

					//dd($articleId);
					/* Declare data containers for Articles */
					$tableRows=array();
					$tableColors=array();
					$staticData=array();
					$dynamicData=array();
					$extraData=array();
					$xlsxRow=array();
					$updateIds[]=$articleId;
					/* Use for to avoid missing keys issue normally header2 will have all keys */
					for($i=0;$i<count($header2);$i++)
					{
						if(isset($header2[$i]) && $header2[$i]!=''){
							if($i==0)
							{
								$value[$i]=$articleId;
								if(!isset($fileColors[$key][$i])){
									$fileColors[$key][$i]=(isset($headerColor[$i]))?$headerColor[$i]:'FFFFFFFF';
								}
							}
							$xlsxRow[]=(isset($value[$i]))?$value[$i]:'';
							$value[$i]=(isset($value[$i]))? preg_replace('/(:\s)/', ':||', $value[$i]):'';
							//$value[$i]=(isset($value[$i]))?str_replace(":",":||",$value[$i]):'';
							$tableRows[]=array((isset($header[$i]))?$header[$i]:'',(isset($header2[$i]))?$header2[$i]:'',(isset($value[$i]))?$value[$i]:'');
							$tableColors[]=array((isset($headerColor[$i]))?$headerColor[$i]:'',(isset($header2Color[$i]))?$header2Color[$i]:'',(isset($fileColors[$key][$i]))?$fileColors[$key][$i]:'FFFFFFFF');

							if(in_array($i,$staticKeys)){
								$staticData[$i]=(isset($value[$i]))?$value[$i]:'';
							}
							if(in_array($i,$dynamicKeys)){
								$dynamicData[$i]=(isset($value[$i]))?$value[$i]:'';
							}
							if(in_array($i,$extraKeys)){
								$extraData[$i]=(isset($value[$i]))?$value[$i]:'';
							}
						}

					}

					//echo "<pre>DY"; print_r($dynamicData);exit;

					$basicLib=new basicLib();
					$filename=$articleId."Err-";
					$fileNameArr=array();

					$fileNameArr=array(0,1,2);

					// Naming the file
					if(!isset($value[$fileNameArr[2]]))
					{
						$value[$fileNameArr[2]] = rand(0,9999);
					}

					if(isset($value[$fileNameArr[0]]) && isset($value[$fileNameArr[1]]) && isset($value[$fileNameArr[2]])){
						$fileName=$articleId.'-'.strip_tags($this->edsLib->normaliseUrlString($value[$fileNameArr[1]])).'-'.strip_tags($this->edsLib->normaliseUrlString($value[$fileNameArr[2]]));
					}

					if(!isset($fileName))
					{
						\Session::flash('error','Template is not in the appropriate format');
						return redirect()->back();
					}
					$fileName=substr($fileName,0,50).'.docx';
					$size=array(500,2000,8130);

					$fileName = $basicLibr->normaliseUrlString($fileName);
					/* Create New Article in Doc */
					// echo "<pre>";
					// print_r($tableRows);echo '<br />';
					// print_r($tableColors);echo '<br />';
					// print_r($fileStoragePath);echo '<br />';
					// print_r($fileName);echo '<br />';
					// print_r($size);echo '<br />'; 
					// exit;

					$this->word->createTableTemplate($tableRows,$tableColors,$fileStoragePath,$fileName,$size,1);
					$tableRows=array();
					$tableColors=array();
					/* Create New Row in DB for article  */
					$articleArray=array(
									array(
										'aui_type'=>$type,
										'aui_static_data'=>json_encode($staticData),
										'aui_dynamic_data'=>json_encode($dynamicData),
										'aui_extra_data'=>json_encode($extraData),
										'aui_imp1_data'=>'',
										'aui_article_stage'=>0,
										'aui_created_at'=>date('Y-m-d H:i:s'),
										'aui_upload_id'=>$upload->upload_id,
										'aui_article_template'=>'products/'.$product.'/templatesTest/'.$folder."/".$fileName,
										'aui_download_id'=>'',
										'aui_imp2_data'=>'',
										'aui_imp3_data'=>''
										)
								  );

					$this->model->insert($articleArray);

					/* Create XLSX array for output */
					$crypted=Crypt::encrypt('products/'.$product.'/templates/'.$folder."/".$fileName);
					$url=md5($crypted);
					$link=url('download/'.Crypt::encrypt('products/'.$product.'/templates/'.$folder."/".$fileName).'/s');
					CepHashList::insert(array(array('hash_md5'=>$url,'hash_text'=>$crypted)));
					$link=url('download/'.$crypted.'/s');

					array_unshift($xlsxRow,$link);
					$xlsxArray[]=$xlsxRow;
					//echo $fileName;

				}
			}



		}
		//exit;
		/* XLSX */
		$fileName = $basicLibr->normaliseUrlString($prefixWriter.$configuration->name."-".uniqid() ."_".$type."_".date('y-m-d-His').".xlsx");
  		$filePath = public_path().'/uploads/products/'.$product.'/templates/'.$fileName ;
		$this->excellibobj->writeExcel($xlsxArray,$filePath,array('A'));
		$path='products/'.$product."/templates/".$fileName;
		// exit;

		//echo $fileName.'<br/>';
		/* Download entry Prepaarations and Entry*/
	  	$productInfo=$this->productHelper->checkProductExists($product);

		//Create Options for Download Entry
	  	$dwdoptions=array(
	  				'name'=>basename($fileName),
	  				'type'=>'writer',
	  				'client'=>$productInfo->client_id,
	  				'product'=>$product,
	  				'item'=>$upload->upload_item_id,
	  				'path'=>$path,
	  				'description'=>'gen file generated'
	  			);

	  	if($this->FileManager->downloadInitiated($dwdoptions)){
	  		$dwd=$this->FileManager->getDwdId(basename($fileName),$upload->upload_item_id);
	  		CepClientArticleModel::whereIn('aui_article_id',$updateIds)
									->update(
											array(
												'aui_article_stage'=>1,
												'aui_download_id'=>$dwd['download_id']
											)
										);
	  		return redirect("download/".$dwd['download_id']);

	  	}

	}

	public function convertToXlsx($id)
	{
		//dd($id);
		// return 'Delivery First Page...';
		$configurations = CepClientTemplateConfigModel::lists('name','id');
		$products = CepProducts::lists('prod_name','prod_id');

        $delivery_uploads = CepUploads::leftjoin('cep_user_plus','upload_by','=','up_user_id')
        					->where('upload_type','=','pre_delivery')
        					->where('upload_product_id','=',$id)
        					// ->where('upload_verification_status','=',0)
        					->select(DB::raw('upload_date as dt,up_first_name,up_last_name,upload_by, upload_original_name,upload_name,upload_url,upload_id,upload_verification_status'))
        					->orderBy('upload_date', 'desc')
        					->get();

		return view('clients_custom.epinternal.delivery',compact('configurations','products','delivery_uploads'));
	}

	public function convertToDocx($id)
	{
		$configurations = CepClientTemplateConfigModel::lists('name','id');
		$products = CepProducts::lists('prod_name','prod_id');

		$delivery_uploads = CepUploads::leftjoin('cep_user_plus','upload_by','=','up_user_id')
								->where('upload_type','=','pre_delivery')
								->where('upload_product_id','=',$id)
								->select(DB::raw('upload_date as dt,up_first_name,up_last_name,upload_by,upload_original_name,upload_name,upload_url,upload_id,upload_verification_status'))
								->orderBy('upload_date','desc')
								->get();

		return view('clients_custom.epinternal.deliverydoc',compact('configurations','products','delivery_uploads'));
	}

	public function deliverdoc()
	{
		// return 'Delivery Second Page Processing .zip file\'s';
		if(Request::isMethod('get'))
        	return redirect()->back()->with('customError', $this->dictionary->msg_templates_process_fail);

		$data = Request::all();
		// Validate
		$validate = Validator::make($data,[
                 		'file' => 'required'
                 	]);
        if($validate->fails())
        {
			return redirect()->back()->withErrors($validate->errors());
		}
		
		// $data = Request::all();
		$item = CepItems::where('item_product_id',$data['products'])->first();
    	$filepath = "";
		$itemData = CepItems::leftjoin('cep_product_users','item_product_id','=','puser_product_id')
							->leftjoin('users','puser_user_id','=','id')
							->leftjoin('cep_groups','cep_groups.group_id','=','users.group_id')
							->where('item_id',$item->item_id)
							->where('group_code','CL')
							->select('id','item_product_id','item_id')
							->first();

        $file = Input::file('file');
		$upload = array();
		if($file) {
	        $name = uniqid();
			$options = array (
		 				'name'=>$name,
		 				'type'=>'custom',
						'description'=> 'custom upload',
						'url'=> 'products/'.$itemData->item_product_id.'/gen/inputs/',
						'client'=> $itemData->id,
						'product'=> $itemData->item_product_id,
						'item'=> $itemData->item_id,
						'reference_column'=> '',
          				'status'=>  1
					);

			$upload = $this->FileManager->upload($file,$options);

			if(!is_null($upload)) {
				$productHelper = new ProductHelper;
				$prod = $productHelper->checkProductExists($itemData->item_product_id);
				$prodUsers = $productHelper->getClientProductUsers($itemData->item_product_id,array('BO'));
				$delivery = $this->processDeliveryDocTest($upload,$data['products'],$itemData->item_id);
			}
		}  

		$prodConfigs = array();
        $writerFileList = CepDownloads::leftjoin('cep_user_plus','download_by','=','up_user_id')
							->where('download_type','=','deliverdocx')
        					->where('download_product_id','=',$data['products'])
        					->orderBy('download_date', 'desc')
							->get()->toArray();
		$time=DB::select(DB::raw('SELECT NOW() AS end_time'));
		$time=$time[0]->end_time;

		$prodConfigs['custom'] = [];
		$deliveries = [];

		return view('clients_custom.epinternal.deliverydoc2',compact('deliveries','writerFileList','prodConfigs','time'));

	}

	public function processDeliveryDocTest($upload,$product,$itemId)
	{
		$request = Request::all();
		$configuration = CepClientTemplateConfigModel::find($request['configuration']);

		$type = 0;
		// Get Dynamic Data
		$dynamicKeys=json_decode($configuration->dynamic_data);
		$dynamicData = array_chunk($dynamicKeys,(int)ceil(count($dynamicKeys)/4));

		// Get Delivery Data
		$dynamicKeys=json_decode($configuration->delivery_data);
		$staticKeys=json_decode($configuration->static_data);
		$headerKey=json_decode($configuration->choose_header);
		$filename=json_decode($configuration->filename);

		$dynamicKeys=array_intersect_key($dynamicKeys, array_filter($dynamicKeys, 'is_numeric'));
		$staticKeys=array_intersect_key($staticKeys, array_filter($staticKeys, 'is_numeric'));
		$headerKey=array_intersect_key($headerKey, array_filter($headerKey, 'is_numeric'));
		$filename=array_intersect_key($filename, array_filter($filename, 'is_numeric'));

		$file=public_path()."/uploads/".$upload->upload_url;
		$pathInfo=pathinfo($file);
		$destFolder=public_path().'/uploads/products/'.$product.'/gen/inputs/'.$pathInfo['filename'];

		$wordReader=new WordReader();
		$basicLib=new basicLib();

		// Get all files unzipped
		$docxFiles=$basicLib->unzip($file,$destFolder);
		$tableColors2 = array(
					array('0' => 'FF0000', '1' => 'FF0000', '2' => 'FF0000', '3' => 'FF0000'),
					array('0' => 'FF0000', '1' => 'FF0000', '2' => 'FF0000', '3' => 'FF0000'),
					array('0' => 'FF0000', '1' => 'FF0000', '2' => 'FFFFFFFF', '3' => 'FF0000')
				);
		$folder=$configuration->name."-".uniqid();
		$fileStoragePath=public_path().'/uploads/products/'.$product.'/templatesTest/'.$folder;
		File::makeDirectory($fileStoragePath, $mode = 0777, true, true);
		$size = array(3000,6000);
		
		foreach ($docxFiles as $key => $value) {
			$pathInfo2 = pathinfo($value);
			$content = $wordReader->readTable($value,false,array(),true,array('','<br>'));
			$xlsxData = array();
			$merge_filenames = array();

			// Get the Header
			foreach ($content as $key => $value) {
				if($key == 0){
					$id = strip_tags($value[2]);
				}
				if(in_array($key,$headerKey) && !empty($value)){
					$header = strip_tags($value[2]);
				}
				if($key == 2){
					$estabid = strip_tags($value[2]);
				}
				if($key == 3){
					$name = strip_tags($value[2]);
					$id_header = $estabid.'-'.$name.'-'.$id;
				}
				if(in_array($key,$filename) && !empty($value)){
					$merge_filenames[$key] = strip_tags($value[2]);
				}
			}
			$new_filename = array();
			$merge_filename = ''; 
			foreach ($filename as $key => $value) {
				$new_filename[] = $merge_filenames[$value];
			}
			$merge_filename = implode("-",$new_filename);

			$offset = count(json_decode($configuration->dynamic_data))/4;
			$data = array_chunk(json_decode($configuration->dynamic_data),$offset);
			$count = 0;
			// Formatting Output
			if($content!=''){
				$dynamicUpdate=array();
				$temp=array();
				foreach ($content as $key2 => $value2) {
					if( in_array($key2,$data[1]) ){
						if( empty($data[3][$count]) ){
							$value2 = strip_tags($value2[2]);
							$dynamicUpdate[$key2] = $this->applyoutputrules($value2,$data[3][$count],$data[0][$count],$data[2][$count]);
						} else {
							$dynamicUpdate[$key2] = $this->applyoutputrules($value2[2],$data[3][$count],$data[0][$count],$data[2][$count]);
						}
						$count++;
					}
				}
			}
			
			array_shift($dynamicUpdate);
			// array_unshift($dynamicUpdate,$id_header);
			array_unshift($dynamicUpdate,$merge_filename);
			$xlsxData[] = $dynamicUpdate;
			$xlsxData = array_values($xlsxData['0']);

			$count_array = count($xlsxData['0']);
			$data = array();
			for ($i=0; $i<$count_array; $i++){
				foreach($xlsxData as $k=>$v){
					$data[] = $v;
				}
				$xlsxData2[]=$data;
			}

			$xlsxData_count = count($xlsxData2['0']);
			$xlsxData_li = array();
			for ($i=0; $i < $xlsxData_count; $i++) {
				$xlsxData_li[] = str_replace("</li>", "</li>\n\r", $xlsxData2['0'][$i]);
			}
			$xlsxData22[] = $xlsxData_li;

			$fileName = strip_tags($xlsxData2[0][0]);
			$fileName = substr($fileName,0,50).'.docx';

			$this->createWordTemplate($xlsxData22,$tableColors2,$fileStoragePath,$fileName,$size);
			$xlsxData2 = array();
			$xlsxData22 = array();
		} 
		// echo '<pre>';
		// print_r($xlsxData22);
		// exit();

		// Uploading Docx file path into DB "cep_downloads" Table
		$productInfo=$this->productHelper->checkProductExists($product);
		$pathInfo = pathinfo($fileStoragePath);
		$fileName = $pathInfo['filename'] . '.zip';
		$fileStoragePath = $fileStoragePath;
		$filePath = public_path().'/uploads/products/'.$product.'/templatesTest/' . $fileName;
		$basicLib->zip_creation($fileStoragePath.'/',$filePath,'docx');
		$filePath = 'products/'.$product.'/templatesTest/' . $fileName;
		$dwdoptions=array( 
					'name'=>basename($fileName),
					'type'=>'deliverdocx',
					'client'=>$productInfo->client_id,
					'product'=>$product,
					'item'=>$upload->upload_item_id, // 199
					'path'=>$filePath,
					'description'=>'gen file generated'
				);
		$this->FileManager->downloadInitiated($dwdoptions);

		/*
		return $xlsxData22; 
		exit();
		foreach ($docxFiles as $key => $value) { 
			$pathInfo2=pathinfo($value);
			$temp=array();

			// get files content after processing
			$content=$wordReader->readTable($value,false,array(),true,array('','<br>'));
			$xlsxData = array(); 
			$columns = array();

			foreach ($content as $key2 => $value2) {
				if(isset($value2[2]))
				{
					$columns[] = $this->applyOutputRules(strip_tags($value2[2]),['h3','h4','list','link','strong','p'],null,null);
				}
			}

			// Get the Header
			foreach ($content as $key => $value) {
				if($key == 0){
					$id = strip_tags($value['2']);
				}
				if(in_array($key,$headerKey) && !empty($value)){
					$header = strip_tags($value['2']);
					$id_header = $id .' - '. $header;
				}
			}

			$offset = count(json_decode($configuration->dynamic_data))/4;
			$data = array_chunk(json_decode($configuration->dynamic_data),$offset);
			$count = 0;

			// Formatting Output
			if($content!=''){
				$dynamicUpdate=array();
				$temp=array();
				foreach ($content as $key2 => $value2) {
					if( in_array($key2,$data[1]) ){
						$dynamicUpdate[$key2] = $this->applyoutputrules(strip_tags($value2[2]),$data[3][$count],$data[2][$count],$data[0][$count]);
						$count++;
					}
				}
			}

			array_shift($dynamicUpdate);
			array_unshift($dynamicUpdate,$id_header);
			$xlsxData[]=$dynamicUpdate;
			$xlsxData = array_values($xlsxData['0']);
		$i = 1;
			$count_array = count($xlsxData['0']);
			$data = array();
			for ($i=0; $i<$count_array; $i++) {
				foreach($xlsxData as $k=>$v){
					$data[] = $v;
				}
				$xlsxData2[]=$data;
			}

			$xlsxData_count = count($xlsxData2['0']);
			for ($i=0; $i < $xlsxData_count; $i++) {
				$xlsxData_li[] = str_replace("</li>", "</li>\n\r", $xlsxData2['0'][$i]);
			}
			$xlsxData22[] = $xlsxData_li;

			$fileName = strip_tags($xlsxData22[0][0]);
			$fileName = substr($fileName,0,50).'.docx';			
			$this->createWordTemplate($xlsxData22,$tableColors2,$fileStoragePath,$fileName,$size);
			$xlsxData2 = array();
			$xlsxData22 = array();
		}

		// Uploading Docx file path into DB "cep_downloads" Table
		$productInfo=$this->productHelper->checkProductExists($product);
		$pathInfo = pathinfo($fileStoragePath);
		$fileName = $pathInfo['filename'] . '.zip';
		$fileStoragePath = $fileStoragePath;
		$filePath = public_path().'/uploads/products/'.$product.'/templatesTest/' . $fileName;
		$basicLib->zip_creation($fileStoragePath.'/',$filePath,'docx');
		$filePath = 'products/'.$product.'/templatesTest/' . $fileName;
		$dwdoptions=array( 
					'name'=>basename($fileName),
					'type'=>'deliverdocx',
					'client'=>$productInfo->client_id,
					'product'=>$product,
					'item'=>$upload->upload_item_id, // 199
					'path'=>$filePath,
					'description'=>'gen file generated'
				);
		$this->FileManager->downloadInitiated($dwdoptions); /**/

	}

	public function createWordTemplate($data,$color,$fileStoragePath,$fileName,$size){
  		$phpWord = new \PhpOffice\PhpWord\PhpWord();

  		// Document style orientation and margin
        // $sectionStyle = array('orientation' => 'portrait', 'marginLeft'=>600, 'marginRight'=>600, 'marginTop'=>600, 'marginBottom'=>600, 'colsNum' => 1);
		$section = $phpWord->addSection();

		// Adding Text element with font customized using explicitly created font style object...
		// $fontStyle = new \PhpOffice\PhpWord\Style\Font();
		$lineHeight = 4;

		$count_data = count($data[0]);
		for ($i=0; $i < $count_data; $i++) {
			if ($i == 0){
				$header_data = strip_tags($data[0][$i]);
				$section->addText($header_data, ['bold'=>true,'size'=>12],[$lineHeight]);
			} elseif (strlen($data[0][$i]) >= 40){
				$paragraph_data = htmlspecialchars($data[0][$i]);
				$section->addText($paragraph_data, ['bold'=>false,'size'=>12],[$lineHeight]);
			} elseif(strlen($data[0][$i]) < 40){
				$header_data = htmlspecialchars($data[0][$i]);
				$section->addText($header_data, ['bold'=>false,'size'=>12],[$lineHeight]);
			}
			$textrun = $section->addTextRun([$lineHeight]);
		}

		// Saving the document as OOXML file...
		$objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
		//ob_clean();
		$objWriter->save($fileStoragePath."/".$fileName);

	}

	public function createDeliveryOutputTest($type,$staticData,$dynamicUpdate, $configuration_id = null)
	{
		return $dynamicUpdate;
		$count = count($staticData) + count($dynamicUpdate);
		for ($j=0; $j < $count; $j++) {
			// foreach ($staticData as $key => $value) {
				// if($j == $key){
					// $data[] = strip_tags($value);
					// $data[] = htmlspecialchars_decode($value);
				// }
			// }
			foreach ($dynamicUpdate as $key => $value) {
				// $data[] = $value;
				// $data[] = htmlspecialchars_decode($value);
				// echo "$j == $key";
				// echo '<br />';
				// if($j == 0 && $key == 0){
				// 	$data[] = $value;
				// }
				if($j == $key){
					$data[] = $value;
					// $data[] = strip_tags($value);
					// $data[] = htmlspecialchars_decode($value);
				}
				// $data[] = strip_tags($value);
			}
		}/**/
		return $data;
	}

	public function processDeliveryDoc($upload,$product,$itemId)
	{
		// return $upload;
		$request = Request::all();
		$configuration = CepClientTemplateConfigModel::find($request['configuration']);
		$this->prodConfigs=$this->productHelper->getProductDevConfigs($product);
		
		$type = 0;
		$dynamicKeys=json_decode($configuration->dynamic_data);
		$dynamicKeys=array_intersect_key($dynamicKeys, array_filter($dynamicKeys, 'is_numeric'));

		$file=public_path()."/uploads/".$upload->upload_url;
		$pathInfo=pathinfo($file);
		$destFolder=public_path().'/uploads/products/'.$product.'/gen/inputs/'.$pathInfo['filename'];

		$wordReader=new WordReader();
		$basicLib=new basicLib();

		// Get all files unzipped
		$docxFiles=$basicLib->unzip($file,$destFolder);

		$xlsxData=array(
						json_decode($configuration->xlsx_headers)
					);
		foreach ($docxFiles as $key => $value) {
			$pathInfo2=pathinfo($value);
			$temp=array();
			// get files content after processing
			$content=$wordReader->readTable($value,false,array(),true,array('','<br>'));

			/* Update DB */
			if($content!=''){
				$dynamicUpdate=array();
				$temp=array();
				foreach ($content as $key2 => $value2) {
					if(in_array($key2,$dynamicKeys) && !empty($value2)){
						$dynamicUpdate[$key2]=$value2[2];
					}
				}

				$article=array();
				if(!empty($dynamicUpdate)){
					$article=$this->model->where('aui_article_id',strip_tags($content[0][2]))->where('aui_article_status',0)->first();
				}

				if($article && !empty($article))
				{
					$article->aui_dynamic_data=json_encode($dynamicUpdate);
					$article->aui_article_stage=1;
					$article->aui_project_id = $request['configuration'];
					$article->save();

					/* Create Xlsx Array */
					$staticData=json_decode($article->aui_static_data);
					$updatedStaticData=array();
					$staticData=(array) $staticData;
					foreach ($staticData as $key => $value) {
						$value=(is_string( $value)) ? $value:'';
						$value=$this->edsLib->correctUnicode($value);
		           		$value=$this->edsLib->unicodeToAscii($value);
		          		$updatedStaticData[$key]= stripslashes(html_entity_decode($value));
					}
					$staticData=$updatedStaticData;
					try
					{
						$temp = $this->createDeliveryOutput($type,$staticData,$dynamicUpdate);
					}catch(Exception $e)
					{
						//echo "Incorrect format!";
						//exit;
						return back()->with('errors',array('Incorrect format!'));
					}

				}
				else
				{
						//echo "Incorrect format!";
						//exit;
					\Session::flash('message','Template is not in the right format');

					return redirect()->back();
				}

			}else{
						//echo "Articles not found";
						//exit;
					\Session::flash('message','Articles not found');
					return redirect()->back();
			}
			$temp[0] = $article->aui_article_id;
			ksort($temp);
			$xlsxData[]=$temp;
			// $temps[]=$temp;
		}

		// =================For testing=================
		echo '<pre>';
		// print_r($temp);
		// print_r($xlsxData);
		// exit();

		$temps = array(
				array('0'=>'ID','1'=>'Col-1','2'=>'Col-2','3'=>'Description'),
				array('0'=>'17975','1'=>'Bangalore','2'=>'IT Companies','3'=>'Test-11 In Bangalore there are around 100 Companies'),
				array('0'=>'17976','1'=>'Hyderabad','2'=>'IT Companies','3'=>'Test-22 In hyderabad there are around 20 companies')
			);

		$tableColors2 = array(
					array('0' => 'FF000000', '1' => 'FF000000', '2' => 'FF000000', '3' => 'FF000000'),
					array('0' => 'FF000000', '1' => 'FF000000', '2' => 'FF000000', '3' => 'FF000000'),
					array('0' => 'FF000000', '1' => 'FF000000', '2' => 'FFFFFFFF', '3' => 'FF000000')
				);
		$prefixWriter= '';
		$folder=$prefixWriter.$configuration->name."-".uniqid();
		$fileStoragePath=public_path().'/uploads/products/'.$product.'/templatesTest/'.$folder;
		File::makeDirectory($fileStoragePath, $mode = 0777, true, true);
		
		$fileName = $temps['0']['1'] . '_' . $temps['1']['2'];
		$fileName = substr($fileName,0,50).'.docx';
		$size = array(500,2000,8130,8130);
	
		foreach($temps as $k => $temp){
			print_r($temp);
			// $this->word->createTableTemplate($temp,$tableColors2,$fileStoragePath,$fileName,$size,1);
		}
		exit();

		// =================For testing=================
		// Write into Docx file
		$tableColors2 = array(
						array('0' => 'FF000000', '1' => 'FF000000', '2' => 'FF000000', '3' => 'FF000000'),
						array('0' => 'FF000000', '1' => 'FF000000', '2' => 'FF000000', '3' => 'FF000000'),
						array('0' => 'FF000000', '1' => 'FF000000', '2' => 'FFFFFFFF', '3' => 'FF000000')
					);

		// Create New folder for storing Docx file/
		$prefixWriter= '';
		// $folder=$prefixWriter.$configuration->name."_Template-".uniqid()."-".rand(1111,9999);
		$folder=$prefixWriter.$configuration->name."-".uniqid();
		$fileStoragePath=public_path().'/uploads/products/'.$product.'/templatesTest/'.$folder;
		File::makeDirectory($fileStoragePath, $mode = 0777, true, true);
		
		$fileName = $xlsxData['1']['1'] . '_' . $xlsxData['2']['1'];
		$fileName = substr($fileName,0,50).'.docx';
		$size = array(500,2000,8130,8130);
		$this->word->createTableTemplate($xlsxData,$tableColors2,$fileStoragePath,$fileName,$size,1);

		// Generating Url
		$crypted=Crypt::encrypt('products/'.$product.'/templatesTest/'.$folder."/".$fileName);
		$url=md5($crypted);
		$link=url('download/'.$crypted.'/s');
				
			// Uploading Docx file path into DB "cep_downloads" Table
			$productInfo=$this->productHelper->checkProductExists($product);
			$path='products/'.$product."/templatesTest/".$folder."/".$fileName;
			$dwdoptions=array( 
						'name'=>basename($fileName),
						'type'=>'deliverdocx',
						'client'=>$productInfo->client_id,
						'product'=>$product,
						'item'=>$upload->upload_item_id, // 199
						'path'=>$path,
						'description'=>'gen file generated'
					);
			if($this->FileManager->downloadInitiated($dwdoptions)){
				return $dwd=$this->FileManager->getDwdId(basename($fileName),$upload->upload_item_id);
				// CepClientArticleModel::whereIn('aui_article_id',$updateIds)
				// 						->update(
				// 								array(
				// 									'aui_article_stage'=>1,
				// 									'aui_download_id'=>$dwd['download_id']
				// 								)
				// 							);
				// return redirect("download/".$dwd['download_id']);
			}/**/

			// Uploading Docx file path into DB "cep_uploads" Table
			// $path='products/'.$product."/templatesTest/".$fileName;
			// $item = CepItems::where('item_product_id',$request['products'])->first();
			// $filepath = "";
			// $itemData = CepItems::leftjoin('cep_product_users','item_product_id','=','puser_product_id')
			// 					->leftjoin('users','puser_user_id','=','id')
			// 					->leftjoin('cep_groups','cep_groups.group_id','=','users.group_id')
			// 					->where('item_id',$item->item_id)
			// 					->where('group_code','CL')
			// 					->select('id','item_product_id','item_id')
			// 					->first();

			// // return $path .'-'. $fileName .'-'. $fileStoragePath;
			// $fileStoragePathProducts='products/'.$product.'/templatesTest/'.$folder;

			// $file = Input::file('file');
			// $upload = array();
			// if($file) {
			// 	$name = uniqid();
			// 	$options = array (
			// 				'name'=>$name,
			// 				'type'=>'deliverdocx',
			// 				'description'=> 'Delivery in Docx',
			// 				// 'url'=> 'products/'.$itemData->item_product_id.'/gen/inputs/',
			// 				'url'=> $fileStoragePathProducts,
			// 				'client'=> $itemData->id,
			// 				'product'=> $itemData->item_product_id,
			// 				'item'=> $itemData->item_id,
			// 				'reference_column'=> '',
			// 				'status'=>  1
			// 			);
			// 	$upload = $this->FileManager->upload($file,$options);
			// }
	}

	/**
	* Date: 31-07-2017
	* Generalized delivery
	*/
	public function delivery()
	{
		// return 'Delivery Second Page Processing .zip file\'s';
		if(Request::isMethod('get'))
        	return redirect()->back()->with('customError', $this->dictionary->msg_templates_process_fail);

		$data = Request::all();
		/* Validate */
		$validate = Validator::make($data,[
                 		'file' => 'required'
                 	]);
        if($validate->fails())
        {
			return redirect()->back()->withErrors($validate->errors());
        }
		$data = Request::all();

		$item = CepItems::where('item_product_id',$data['products'])->first();
		//dd($data['products']);
    	$filepath = "";
		$itemData = CepItems::leftjoin('cep_product_users','item_product_id','=','puser_product_id')
							->leftjoin('users','puser_user_id','=','id')
							->leftjoin('cep_groups','cep_groups.group_id','=','users.group_id')
							->where('item_id',$item->item_id)
							->where('group_code','CL')
							->select('id','item_product_id','item_id')
							->first();

        $file=Input::file('file');
        //print_r($file);
		$upload=array();
		if($file)
		{
	        $name=uniqid();
			$options =array (
		 				'name'=>$name,
		 				'type'=>'custom',
						'description'=> 'custom upload',
						'url'=> 'products/'.$itemData->item_product_id.'/gen/inputs/',
						'client'=> $itemData->id,
						'product'=> $itemData->item_product_id,
						'item'=> $itemData->item_id,
						'reference_column'=> '',
          				'status'=>  1
						 ); 

			$upload=$this->FileManager->upload($file,$options);


			if(!is_null($upload))
			{
				$productHelper=new ProductHelper;
				$prod=$productHelper->checkProductExists($itemData->item_product_id);
				$prodUsers=$productHelper->getClientProductUsers($itemData->item_product_id,array('BO'));

				/* Activity Alerts */
				//echo "<pre>"; print_r($prodUsers);exit;
				$variable = '{ "EM_PRODUCT ": "'.$prod->name.'"}';

				/* Activity Custom */

		        $variable_act = '{ "AC_PRODUCT": "'.$prod->name .'"}';
				$act_options = array(
		                            'act_template_id' => $this->configs->template_creation_activity_template_id,
		                            'act_by' => Auth::id(),
		                            'act_product_id' => $itemData->item_product_id,
		                            'act_variables' => $variable_act
		                           );
		        $this->templatesactivity->postActivity($act_options);

                /* Close Activity Custom */
				/* Close Activity Email Alerts */

				if(isset($data['custom_delivery']) && $data['custom_delivery'] == 'seton')
				{
					$delivery = $this->setonDeliveryProcess($upload,$data['products'],$itemData->item_id);
				}
				else
				{
					$delivery=$this->processDelivery($upload,$data['products'],$itemData->item_id);
				}

			}
		}
    //dd($delivery);
		return back()->with('success', array('Delivery file Generated Successfully '));
	}//End of delivery

	/**
	 * Function processDelivery
	 *
	 * @param
	 * @return
	 */
	public function processDelivery($upload,$product,$itemId)
	{
		$request = Request::all();
		$configuration = CepClientTemplateConfigModel::find($request['configuration']);
		//dd($cofiguration);
		$this->prodConfigs=$this->productHelper->getProductDevConfigs($product);
		//$type=$this->prodConfigs['pdc_client_article_type'];
		$type = 0;
		//echo "INPUT DONE PROCESSING PENDING";
		$dynamicKeys=json_decode($configuration->dynamic_data);
		$dynamicKeys=array_intersect_key($dynamicKeys, array_filter($dynamicKeys, 'is_numeric'));

		$file=public_path()."/uploads/".$upload->upload_url;
		$pathInfo=pathinfo($file);
		$destFolder=public_path().'/uploads/products/'.$product.'/gen/inputs/'.$pathInfo['filename'];
		//echo "<pre>"; print_r($pathInfo);
		//echo "<pre>"; print_r($destFolder);exit;
		$wordReader=new WordReader();
		//$model=new CepClientLeparisianArticles();
		$basicLib=new basicLib();
		/* Get all files unzipped */
		$docxFiles=$basicLib->unzip($file,$destFolder);
		//echo "<pre>"; print_r($docxFiles);

		$xlsxData=array(
						json_decode($configuration->xlsx_headers)
					);
		foreach ($docxFiles as $key => $value) {
			$pathInfo2=pathinfo($value);
			$temp=array();
			//echo mime_content_type($value) . "\n";
			/* get files content after processing  */
			$content=$wordReader->readTable($value,false,array(),true,array('','<br>'));
			//var_dump($content);
			//echo "<pre>"; print_r($content);
			/* Update DB */
			if($content!='')
			{

				$dynamicUpdate=array();
				$temp=array();
				foreach ($content as $key2 => $value2) {

					if(in_array($key2,$dynamicKeys) && !empty($value2)){

						$dynamicUpdate[$key2]=$value2[2];
					}
				}
				//echo "<pre>"; print_r($dynamicUpdate);
				$article=array();
				if(!empty($dynamicUpdate)){
					$article=$this->model->where('aui_article_id',strip_tags($content[0][2]))->where('aui_article_status',0)->first();
				}
				//dd($article);
				//echo "<pre>"; print_r($article);

				if($article && !empty($article))
				{
					$article->aui_dynamic_data=json_encode($dynamicUpdate);
					$article->aui_article_stage=1;
					$article->aui_project_id = $request['configuration'];
					$article->save();

					/* Create Xlsx Array */
					$staticData=json_decode($article->aui_static_data);
					$updatedStaticData=array();
					$staticData=(array) $staticData;

					//echo "<pre>"; print_r($staticData);exit;
					foreach ($staticData as $key => $value) {
						$value=(is_string( $value)) ? $value:'';
						$value=$this->edsLib->correctUnicode($value);
		           		$value=$this->edsLib->unicodeToAscii($value);
		          		$updatedStaticData[$key]= stripslashes(html_entity_decode($value));
					}
					$staticData=$updatedStaticData;
					//echo "<pre>"; print_r($staticData);
					//echo "<pre>"; print_r($dynamicUpdate);
					try
					{
						$temp = $this->createDeliveryOutput($type,$staticData,$dynamicUpdate);
					}catch(Exception $e)
					{
						//echo "Incorrect format!";
						//exit;
						return back()->with('errors',array('Incorrect format!'));
					}

				}
				else
				{
						//echo "Incorrect format!";
						//exit;
					\Session::flash('message','Template is not in the right format');

					return redirect()->back();
				}

			}else{
						//echo "Articles not found";
						//exit;
					\Session::flash('message','Articles not found');
					return redirect()->back();
			}
			//dd($article);
			$temp[0] = $article->aui_article_id;
			ksort($temp);
			//echo "<pre>"; print_r($temp);
			$xlsxData[]=$temp;
		}
		//echo "<pre>"; print_r($xlsxData); exit;
		//echo "------------------------------";
		/* Get Write Path */
		$basicLibr = new BasicLib();
    $fileName = $basicLibr->normaliseUrlString("Delivery_".$configuration->name."_".($type+1)."-".uniqid() ."_".rand(00,99).".xlsx" );
    $filePath = public_path().'/uploads/products/'.$product.'/gen/' ;
    $filePathUploads = '/products/'.$product.'/gen/' ;


    $this->excellibobj->writeExcel($xlsxData,$filePath.$fileName);
    /* Download entry Prepaarations and Entry*/

		/* Create Xlsx File Done   */
		$itemData = CepItems::leftjoin('cep_product_users','item_product_id','=','puser_product_id')
							->leftjoin('users','puser_user_id','=','id')
							->leftjoin('cep_groups','cep_groups.group_id','=','users.group_id')
							->where('item_id',$itemId)
							->where('group_code','CL')
							->select('id','item_product_id','item_id')
							->first();
		$name=uniqid();
		$fileInfo=pathinfo($fileName);
		$options =array (
	 				'name'=>$fileInfo['filename'],
	 				'file'=>basename($fileName),
	 				'type'=>'pre_delivery',
					'description'=> 'pre delviery upload ',
					'url'=> $filePathUploads,
					'client'=> $itemData->id,
					'product'=> $itemData->item_product_id,
					'item'=> $itemData->item_id,
					'reference_column'=> '',
      				'status'=> 1
					);

		$upload=$this->FileManager->uploadEntry($options);
		return ;
	}


	/**
	 * Function createDeliveryOutput
	 *
	 * @param
	 * @return
	 */
	public function createDeliveryOutput($type,$staticData,$dynamicUpdate, $configuration_id = null)
	{

		$request = Request::all();
		if(is_null($configuration_id))
		{
			$configuration = CepClientTemplateConfigModel::find($request['configuration']);
		}
		else
		{
			$configuration = CepClientTemplateConfigModel::find($configuration_id);
		}

		// Get the rules from the JSON encoded dynamic data
		$dynamic_data = json_decode($configuration->dynamic_data);

		$offset = count($dynamic_data)/4;
		//dd($offset);
		$offset = (int)$offset;
		$preCount = 0;
		for($count = 0; $count < $configuration->column_count; $count++)
		{
			if(in_array($count, array_keys($staticData)))
			{
				$temp[$count] = $staticData[$count];
			}
			if(in_array($count, array_keys($dynamicUpdate)))
			{
				//var_dump($preCount+($offset*3));
				if(in_array("paragraph_with_break",$dynamic_data[($preCount+($offset*3))]) || in_array("break",$dynamic_data[($preCount+($offset*3))]) || in_array("paragraph",$dynamic_data[($preCount+($offset*3))]) || in_array("bbcode_paragraph",$dynamic_data[($preCount+($offset*3))]))
				{
					$dData = $dynamicUpdate[$count];
				}
				else
				{
					$dData = strip_tags($dynamicUpdate[$count]);
				}
				$temp[$count] = $this->applyOutPutRules($dData,$dynamic_data[($preCount+($offset*3))],$dynamic_data[$preCount],$dynamic_data[($preCount+($offset*2))]);
				$preCount = $preCount+1;
			}
		}

		return $temp;
	}

	public function applyOutPutRules($value,$rules,$pre,$post)
	{
		//$value = preg_replace('/(\[.[^\[\]]*)(#)(.[^\[\]]*\])/',"$1!-!$3",$value);
		if(!empty($rules))
		{
			// if(in_array('list',$rules))
			// {
			// 	$value = $this->strip_selected_tags($value);
			// }

			//$value = preg_replace('/(\[.[^\[\]]*)(#)(.[^\[\]]*\])/',"$1!-!$3",$value);

			if(in_array('h4',$rules))
			{
        		$value = preg_replace('/(\#\#\#\#)(.[^*]+)(\#\#\#\#)/', "<h4>$2</h4>", $value);
			}
			if(in_array('bbcode_h4',$rules))
			{
        		$value = preg_replace('/(\#\#\#\#)(.[^*]+)(\#\#\#\#)/', "[h4]$2[/h4]", $value);
			}
			if(in_array('h3',$rules))
			{
        		// $value = preg_replace('/(\#\#\#)(.[^*]+)(\#\#\#)/', "<h3>$2</h3>", $value);
        		$value = preg_replace('/(\#\#\#)([^\#\#\#]*)(\#\#\#)/', "<h3>$2</h3>", $value);
			}
			if(in_array('bbcode_h3',$rules))
			{
        		$value = preg_replace('/(\#\#\#)(.[^*]+)(\#\#\#)/', "[h3]$2[/h3]", $value);
			}
			if(in_array('h2',$rules))
			{
        		$value = preg_replace('/(\#\#)(.[^*]+)(\#\#)/', "<h2>$2</h2>", $value);
			}
			if(in_array('bbcode_h2',$rules))
			{
        		$value = preg_replace('/(\#\#)(.[^*]+)(\#\#)/', "[h2]$2[/h2]", $value);
			}
			if(in_array('italic',$rules))
			{
				if($pre != '<italic>')
				{
					$value = preg_replace('/(\#\i)(.[^*]+)(\#\i)/i', "<italic>$2</italic>", $value);
				}
			}
			if(in_array('h1',$rules))
			{
        		$value = preg_replace('/(\#)(.[^*]+)(\#)/', "<h1>$2</h1>", $value);
			}
			if(in_array('bbcode_h1',$rules))
			{
        		$value = preg_replace('/(\#)(.[^*]+)(\#)/', "[h1]$2[/h1]", $value);
			}
			if(in_array('paragraph',$rules))
			{
				//$value = preg_replace('/([\r\n])(.[^*]+)/i','</p><p>$2',$value);
				if($pre != "<h1>" && $pre != "<h2>" && $pre != "<h3>" && $pre != "<h4>" && $pre != "<h5>")
				{
					$value = preg_replace('/(<br>)/', "</p><p>", $value);
				}

				//var_dump($value);
				//echo "<hr/>";
        		//$value = preg_replace('/(\*\*)(.[^#]+)(\*\*)/i', "<p>$2</p>", $value);
			}
			if(in_array('bbcode_paragraph',$rules))
			{
				if($pre != "<h1>" && $pre != "<h2>" && $pre != "<h3>" && $pre != "<h4>" && $pre != "<h5>")
				{
					$value = preg_replace('/(<br>)/', "[/p][p]", $value);
				}

			}
			if(in_array('strong',$rules))
			{
				if($pre != '<strong>')
				{
					$value = preg_replace('/(\*\*)(.[^*]+)(\*\*)/', "<strong>$2</strong>", $value);
					$value = preg_replace('/(\*)(.[^*]+)(\*)/', "<strong>$2</strong>", $value);
				}

			}
			if(in_array('b_bold',$rules))
			{
				if($pre != '<b>')
				{
					$value = preg_replace('/(\*\*)(.[^*]+)(\*\*)/', "<b>$2</b>", $value);
					$value = preg_replace('/(\*)(.[^*]+)(\*)/', "<b>$2</b>", $value);
				}

			}
			if(in_array('bbcode_strong',$rules))
			{
				if($pre != '<strong>')
				{
					$value = preg_replace('/(\*\*)(.[^*]+)(\*\*)/', "[strong]$2[/strong]", $value);
					$value = preg_replace('/(\*)(.[^*]+)(\*)/', "[strong]$2[/strong]", $value);
				}

			}
			if(in_array('bold',$rules))
			{
				if($pre != '<bold>')
				{
					$value = preg_replace('/(\*\*)(.[^*]+)(\*\*)/', "<bold>$2</bold>", $value);
					$value = preg_replace('/(\*)(.[^*]+)(\*)/', "<bold>$2</bold>", $value);
				}

			}
			if(in_array('bullet_underscore',$rules))
			{
				if($pre != "<h1>" && $pre != "<h2>" && $pre != "<h3>" && $pre != "<h4>" && $pre != "<h5>" && $pre != "<p>")
				{
					$value = "[p]".$value."[/p]";
				}

				$value = preg_replace('/(\_)(.[^*]+?)(\_)/',"[li]$2[/li]",$value);
				$value = preg_replace('/(\[li\])(.[^*]+)(\[\/li\])/',"[ul][li]$2[/li][/ul]",$value);
				$value = preg_replace('/(\[\/li\])(.[^*]+)(\[li\])/',"[/li]$2[li]",$value);
				$value = preg_replace('/(<br>)/',"",$value);
			}
			if(in_array('list',$rules))
			{

        		$value = preg_replace('/(\+)(.[^*]+?)(\+)/i', "<li>$2</li>", $value);
        		$value = preg_replace('/(<li>)(.[^*]+)(<\/li>)/i', "<ul><li>$2</li></ul>", $value);

		        // replace unwanted <br> tags//
		        $value = preg_replace( "/(<\/li><br><li>)/i", "</li><li>", $value );
		        $value = preg_replace( "/(\+<br>\+)/i", "</li><li>", $value );

		        //Replace the additional + before header tags
		        // $value = preg_replace( "/(\+<h1>)/i", "</li><li><h1>", $value );
		        // $value = preg_replace( "/(\+<h2>)/i", "</li><li><h2>", $value );
		        // 	// $value = preg_replace( "/(\+<h3>)/i", "</li><li><h3>", $value );
		        // 	$value = preg_replace( "/(\+<h3>)/i", "</li><h3>", $value );
		        // $value = preg_replace( "/(\+<h4>)/i", "</li><li><h4>", $value );
		        // $value = preg_replace( "/(\+<h5>)/i", "</li><li><h5>", $value );

		        // //Replace additional + after header tags
		        // $value = preg_replace( "/(<\/h1>\+)/i", "</h1></li><li>", $value );
		        // $value = preg_replace( "/(<\/h2>\+)/i", "</h1></li><li>", $value );
		        // $value = preg_replace( "/(<\/h3>\+)/i", "</h3></li><li>", $value );
		        // $value = preg_replace( "/(<\/h4>\+)/i", "</h1></li><li>", $value );
		        // $value = preg_replace( "/(<\/h5>\+)/i", "</h1></li><li>", $value );
		        //$value = str_replace('++', "</li><li>", $value);
		        //$value = str_replace('+ <br>+', "</li><li>", $value);
		        //$value = str_replace('+<br>+', "</li><li>", $value);

		        preg_match('/(<ul>)(.[^*]+)(<\/ul>)/i', $value, $matches);
		        foreach($matches as $match){
		            $replaced_match =  preg_replace('/(<p>)|(<\/p>)/i', "", $match);
		            $value = str_replace($match, $replaced_match, $value);
		        }
				$value = preg_replace( "/(\+\+)/i", "</li><li>", $value );
				$value = preg_replace( "/(\+\ \+)/i", "</li><li>", $value );
			}
			if(in_array('list_ol',$rules))
			{
        		$value = preg_replace('/(\+\+)(.[^*]+)(\+\+)/i', "<li>$2</li>", $value);
        		$value = preg_replace('/(<li>)(.[^*]+)(<\/li>)/i', "<ol><li>$2</li></ol>", $value);
		        // replace unwanted <br> tags//
		        $value = preg_replace( "/(<\/li><br><li>)/i", "</li><li>", $value );
		        $value = preg_replace( "/(\+<br>\+)/i", "</li><li>", $value );
		        //$value = str_replace('++', "</li><li>", $value);
		        //$value = str_replace('+ <br>+', "</li><li>", $value);
		        //$value = str_replace('+<br>+', "</li><li>", $value);

		        preg_match('/(<ol>)(.[^*]+)(<\/ol>)/i', $value, $matches);
		        foreach($matches as $match){
		            $replaced_match =  preg_replace('/(<p>)|(<\/p>)/i', "", $match);
		            $value = str_replace($match, $replaced_match, $value);
		        }
				$value = preg_replace( "/(\+\+\+\+)/i", "</li><li>", $value );
				$value = preg_replace( "/(\+\+\ \+\+)/i", "</li><li>", $value );
			}
			if(in_array('bbcode_bullet',$rules))
			{
        		$value = preg_replace('/(\+)(.[^*]+)(\+)/i', "[li]$2[/li]", $value);
        		$value = preg_replace('/(\[li\])(.[^*]+)(\[\/li\])/i', "[ul][li]$2[/li][/ul]", $value);
		        // replace unwanted <br> tags//
		        $value = preg_replace( "/(\[\/li\]<br>\[li\])/i", "[/li][li]", $value );
		        $value = preg_replace( "/(\+<br>\+)/i", "[/li][li]", $value );

		        //$value = str_replace('++', "</li><li>", $value);
		        //$value = str_replace('+ <br>+', "</li><li>", $value);
		        //$value = str_replace('+<br>+', "</li><li>", $value);

		        preg_match('/(\[ul\])(.[^*]+)(\[\/ul\])/i', $value, $matches);
		        foreach($matches as $match){
		            $replaced_match =  preg_replace('/(\[p\])|(\[\/p\])/i', "", $match);
		            $value = str_replace($match, $replaced_match, $value);
		        }
				$value = preg_replace( "/(\+\+)/i", "[/li][li]", $value );
				$value = preg_replace( "/(\+\ \+)/i", "[/li][li]", $value );

			}
			if(in_array('bullet',$rules))
			{

        		$value = preg_replace('/(\+)(.[^*]+)(\+)/i', "<bullet>$2</bullet>", $value);
				$value = preg_replace( "/(\+\+)/i", "</bullet><bullet>", $value );
			}
			if(in_array('link',$rules))
			{
				$value=preg_replace("/(\[(.[^\]\]]+),(.[^\[\]]+)\])/", '<a href="'.trim("$3").'">$2</a>' , $value);

			}
			if(in_array('bbcode_link',$rules))
			{
				// $value=preg_replace("/(\[(.[^\]\]]+),(.[^\[\]]+)\])/", '[url href="'.trim("$3").'" target="_blank"]$2[/url]' , $value);
				$value=preg_replace("/(\[(.[^\]\]]+),(.[^\[\]]+)\])/", '[a href="'.trim("$3").'"]$2[/a]' , $value);
				$value=str_replace(array('http:','https:'),'',$value);
			}
			if(in_array('linkNewTab',$rules))
			{
				$value=preg_replace("/(\[(.[^\]\]]+),(.[^\[\]]+)\])/", '<New_Window/Tab target="'.trim("$3").'">$2</New_Window/Tab>' , $value);
			}


		}


        //$value = preg_replace( "/<br>$/i", "", $value );
        $value=str_replace("!-!","#",$value) ;

        if(in_array('paragraph',$rules))
        {
			if($pre != "<h1>" && $pre != "<h2>" && $pre != "<h3>" && $pre != "<h4>" && $pre != "<h5>")
			{
				// Check for html tags
	        	$value = "<p>".$value."</p>";

	        	// Escaping the headers out of the paragraph tags
	        	$value = preg_replace("/(<h1>)/i","</p><h1>",$value);
	        	$value = preg_replace("/(<h2>)/i","</p><h2>",$value);
	        	$value = preg_replace("/(<h3>)/i","</p><h3>",$value);
	        	$value = preg_replace("/(<h4>)/i","</p><h4>",$value);
	        	$value = preg_replace("/(<h5>)/i","</p><h5>",$value);
	        	$value = preg_replace("/(<\/h1>)/i","</h1><p>",$value);
	        	$value = preg_replace("/(<\/h2>)/i","</h2><p>",$value);
	        	$value = preg_replace("/(<\/h3>)/i","</h3><p>",$value);
	        	$value = preg_replace("/(<\/h4>)/i","</h4><p>",$value);
	        	$value = preg_replace("/(<\/h5>)/i","</h5><p>",$value);

				$value = preg_replace('/(<p><\/p>)/i', "", $value);
			}
			else
			{
				$value = strip_tags($value);
			}

			if(in_array('list',$rules))
			{
				$value = preg_replace("/(<p><li>)/i","<p><ul><li>",$value);
				$value = preg_replace("/(<\/li><\/p>)/i","</li></ul></p>",$value);	
			}
        }

        if(in_array('bbcode_paragraph',$rules))
        {
			if($pre != "[h1]" && $pre != "[h2]" && $pre != "[h3]" && $pre != "[h4]" && $pre != "[h5]")
			{
				// Check for html tags
	        	$value = "[p]".$value."[/p]";

	        	// Escaping the headers out of the paragraph tags
	        	$value = preg_replace("/(\[h1\])/i","[/p][h1]",$value);
	        	$value = preg_replace("/(\[h2\])/i","[/p][h2]",$value);
	        	$value = preg_replace("/(\[h3\])/i","[/p][h3]",$value);
	        	$value = preg_replace("/(\[h4\])/i","[/p][h4]",$value);
	        	$value = preg_replace("/(\[h5\]>)/i","[/p][h5]",$value);
	        	$value = preg_replace("/(\[\/h1\])/i","[/h1][p]",$value);
	        	$value = preg_replace("/(\[\/h2\])/i","[/h2][p]",$value);
	        	$value = preg_replace("/(\[\/h3\])/i","[/h3][p]",$value);
	        	$value = preg_replace("/(\[\/h4\])/i","[/h4][p]",$value);
	        	$value = preg_replace("/(\[\/h5\])/i","[/h5][p]",$value);

				$value = preg_replace('/(\[p\]\[\/p\])/i', "", $value);
			}
			else
			{
				$value = strip_tags($value);
			}
        }

			$value = preg_replace('/(<p><p>)/i', "<p>", $value);
			$value = preg_replace('/(<\/p> <\/p>)/', "</p>", $value);

			$value = preg_replace('/(<strong><strong>)/i', "<strong>", $value);
			$value = preg_replace('/(<\/strong> <\/strong>)/i', "</strong>", $value);

			$value = preg_replace('/(<h1><h1>)/i', "<h1>", $value);
			$value = preg_replace('/(<\/h1> <\/h1>)/i', "</h1>", $value);
			$value = preg_replace('/(<h2><h2>)/i', "<h2>", $value);
			$value = preg_replace('/(<\/h2> <\/h2>)/i', "</h2>", $value);
			$value = preg_replace('/(<h3><h3>)/i', "<h3>", $value);
			$value = preg_replace('/(<\/h3> <\/h3>)/i', "</h3>", $value);
			$value = preg_replace('/(<h4><h4>)/i', "<h4>", $value);
			$value = preg_replace('/(<\/h4> <\/h4>)/i', "</h4>", $value);
			$value = preg_replace('/(<\i> <\i>)/i', "<i>", $value);
			$value = preg_replace('/(<\/i> <\/i>)/i', "</i>", $value);
			$value = preg_replace('/(<ul><ul>)/i', "<ul>", $value);
			$value = preg_replace('/(<ol><ol>)/i', "<ol>", $value);
			$value = preg_replace('/(<\/ul> <\/ul>)/i', "</ul>", $value);
			$value = preg_replace('/(<\/ol> <\/ol>)/i', "</ol>", $value);

			//BBCode
			$value = preg_replace('/(\[p\]\[p\])/i', "[p]", $value);
			$value = preg_replace('/(\[\/p\] \[\/p\])/', "[/p]", $value);
        if(in_array('bbcode_paragraph',$rules) && in_array('bbcode_bullet',$rules))
        {
			$value = preg_replace( "/(\+\[\/p\]\[p\]\+)/i", "", $value );
			$value = preg_replace("/(\[\/p\]\[p\]\[ul\]\[li\])/i","[ul][li]",$value);
			$value = preg_replace("/(\[\/li\]\[\/ul\]\[\/p\]\[p\])/i","[/li][/ul]",$value);
		}

        if(in_array('paragraph_with_break',$rules))
        {
          $value = "<p>".$value."</p>";
          $value = str_replace('</p><p>','<br/>',$value);
          $value = str_replace('<br/></p>','</p>',$value);
          $value = str_replace('<p><p>','<p>',$value);
          $value = str_replace('</p></p>','</p>',$value);
          // Replacing lists
          $value = str_replace('<ul>','</p><ul>',$value);
          $value = str_replace('</ul></p>','</ul>',$value);
          $value = str_replace('<ol>','</p><ol>',$value);
          $value = str_replace('</ol></p>','</ol>',$value);
          //dd($value);
        }

        //Stripping unnecessary tags  insude List
        // if(in_array('list',$rules))
        // {
        //   $this->strip_selected_tags($value,'<h1>');
        // }
        if(in_array('break',$rules))
        {
          $value = preg_replace('/(<br\/>)+$/', '', $value);
            $value = preg_replace('/(<br>)+$/', '', $value);
        }
        if(!empty($pre))
        {
        	$value = $pre.$value;
        }

        if(!empty($post))
        {
        	$value = $value.$post;
        }
		//echo "<hr/>";
        return htmlspecialchars_decode($value);
    }

    function get_num_of_words($string) {
	    $string = preg_replace('/\s+/', ' ', trim($string));
	    $words = explode(" ", $string);
	    return count($words);
	}

  /**
  * DATE: 14-02-2018
  * Strip the selected tags from the text and  return back the text
  */
  public function strip_selected_tags($text)
  {
    $text = str_replace('<br>','',$text);
    $text = str_replace('<br/>','',$text);
    $count = preg_match('/(\+)(.[^*]+)(\#+)(\+)/',$text,$matches);
    $replaced_text = str_replace('#','',$matches[0]);
    $text = preg_replace('/(\+)(.[^*]+)(\#+)(\+)/',$replaced_text,$text);
    return $text;
    // dd($text);
    // //dd(explode('+',$text));
    // //dd($text);
    // $text = explode('+',$text);
    // dd($text);
    // for($i = 1; $i < count($text); $i++)
    // {
    //   $text[$i] = str_replace('#','',$text[$i]);
    // }
    // return implode('+',$text);
  }//End of strip_selected_tags

	public function export($id)
	{
		if( !$this->checkaccess->productAccessCheck($id) || !$this->productHelper->checkProductExists($id))
			return redirect('accessDenied');
		$templates='';
		$prodConfigs=array();
		$product = $this->productHelper->checkProductExists($id);
		$prodConfigs['custom']=CepProductConfigurations::where('pconf_type','=','custom')
										 ->where('pconf_product_id','=',$id)
										 ->first();
		$prodConfigs['devConf']=$this->productHelper->getProductDevConfigs($id);

        $prodConfigs['downloads_verifed'] = CepDownloads::leftjoin('cep_user_plus','download_by','=','up_user_id')
        					->where('download_type','=','extract')
        					->where('download_product_id','=',$id)
        					->where('download_status','=',1)
        					->select(DB::raw('date_format(download_date,"%d, %M %y %h:%i %p") as dt,up_first_name,up_last_name,download_by,download_name,download_url,download_id,download_status'))
        					->orderBy('download_date', 'desc')
        					->get();
		$configurations = CepClientTemplateConfigModel::lists('name','id');
        return view("clients_custom.epinternal.export",compact('templates','prodConfigs','product','configurations'));
	}

	public function exportProcess($id)
	{

		if( !$this->checkaccess->productAccessCheck($id) || !$this->productHelper->checkProductExists($id))
		{
			return redirect('accessDenied');
		}

		$data = Request::all();
		$final_data = array();
		$configuration = CepClientTemplateConfigModel::find($data['config']);
		$final_data[] = json_decode($configuration->xlsx_headers,true);
		$articles = $configuration->article;
		if(!is_null($articles))
		{
			foreach($articles as $article)
			{
				// Get the rules from the JSON encoded dynamic data
				$dynamic_data = json_decode($configuration->dynamic_data);
				$staticData = json_decode($article->aui_static_data,true);
				$dynamicUpdate  = json_decode($article->aui_dynamic_data,true);

				$offset = count($dynamic_data)/4;
				//dd($offset);
				$offset = (int)$offset;
				$preCount = 0;
				for($count = 0; $count < $configuration->column_count; $count++)
				{
					if(in_array($count, array_keys($staticData)))
					{
						$temp[$count] = $staticData[$count];
					}
					if(in_array($count, array_keys($dynamicUpdate)))
					{
						//var_dump($preCount+($offset*3));
						if(in_array("break",$dynamic_data[($preCount+($offset*3))]) || in_array("paragraph",$dynamic_data[($preCount+($offset*3))]))
						{
							$dData = $dynamicUpdate[$count];
						}
						else
						{
							$dData = strip_tags($dynamicUpdate[$count]);
						}
						$temp[$count] = $this->applyOutPutRules($dData,$dynamic_data[($preCount+($offset*3))],$dynamic_data[$preCount],$dynamic_data[($preCount+($offset*2))]);
						$preCount = $preCount+1;
					}
				}

				$final_data[] = $temp;
				$temp = array();
				//dd(json_decode($article->aui_dynamic_data,true));
			}
		}

		if (!file_exists(public_path().'/uploads/products/'.$id.'/export/'.$configuration->name.'/'))
		{
		    mkdir(public_path().'/uploads/products/'.$id.'/export/'.$configuration->name.'/', 0777, true);
		}
		$basicLibr = new BasicLib();
        $fileName=$basicLibr->normaliseUrlString($configuration->name."-EXPORT-".uniqid()."-".rand(00,99).".xlsx");
        $file=public_path().'/uploads/products/'.$id.'/export/'.$configuration->name.'/'.$fileName;
		$path='products/'.$id."/export/".$configuration->name.'/'.$fileName;
		$productInfo=$this->productHelper->checkProductExists($id);
		/* Add the data to Excel sheet */
		$excellibobj = new ExcelLib;
		$excellibobj->writeExcel($final_data,$file);

		$dwdoptions=array(
	  				'name'=>basename($file),
	  				'type'=>'extract',
	  				'client'=>$productInfo->client_id,
	  				'product'=>$id,
	  				'item'=>'',
	  				'path'=>$path,
	  				'description'=>'Export file generated'
	  			);

	  	$this->FileManager->downloadInitiated($dwdoptions);

		return back();

	}// End of exportProcess


    /**
    * Date: 08-11-2017
    * $id; product ID
    * load the view for SETON Delivery
    */
    public function setonDelivery($id)
    {
		$products = CepProducts::lists('prod_name','prod_id');
		$configs = CepClientTemplateConfigModel::lists('name','id');

        $delivery_uploads = CepUploads::leftjoin('cep_user_plus','upload_by','=','up_user_id')
        					->where('upload_type','=','pre_delivery')
        					->where('upload_product_id','=',$id)
        					->where('upload_verification_status','=',0)
        					->select(DB::raw('date_format(upload_date,"%d/%m/%y %h:%i:%s") as dt,up_first_name,up_last_name,upload_by, upload_original_name,upload_name,upload_url,upload_id,upload_verification_status'))
        					->orderBy('upload_date', 'desc')
        					->get();


		if(!$this->productHelper->checkProductExists($id))
		{
			return redirect('accessDenied');
		}

		 foreach($configs as $key=>$config)
		{

		 	if(strpos(strtolower($config),'seton') !== FALSE)
		 	{
		 		$configurations[$key] = $config;
		 	}

		 }

		return view('clients_custom.epinternal.setonDelivery',compact('products','delivery_uploads','configurations'));

    }//End of setonDelivery

    public function setonDeliveryProcess($upload,$product,$itemId)
    {
    	$prodConfigs = $this->productHelper->getProductDevConfigs($product);
    	$headers = array_map('trim',explode(',',$prodConfigs['pdc_seton_delivery']));
    	//$seton_config_id = '48';
		$request = Request::all();
    	$seton_config_id = $request['configuration'];
		$configuration = CepClientTemplateConfigModel::find($seton_config_id);
		//dd($cofiguration);
		$this->prodConfigs=$this->productHelper->getProductDevConfigs($product);
		//$type=$this->prodConfigs['pdc_client_article_type'];
		$type = 0;
		//echo "INPUT DONE PROCESSING PENDING";
		$dynamicKeys=json_decode($configuration->dynamic_data);
		$dynamicKeys = array_intersect_key($dynamicKeys, array_filter($dynamicKeys, 'is_numeric'));

		$file=public_path()."/uploads/".$upload->upload_url;
		$pathInfo=pathinfo($file);
		$destFolder=public_path().'/uploads/products/'.$product.'/gen/inputs/'.$pathInfo['filename'];
		//echo "<pre>"; print_r($pathInfo);
		//echo "<pre>"; print_r($destFolder);exit;
		$wordReader=new WordReader();
		//$model=new CepClientLeparisianArticles();
		$basicLib=new basicLib();
		/* Get all files unzipped */
		$docxFiles=$basicLib->unzip($file,$destFolder);
		//echo "<pre>"; print_r($docxFiles);

		$xlsxData=array(
						json_decode($configuration->xlsx_headers)
					  );
		foreach ($docxFiles as $key => $value) {
			$pathInfo2=pathinfo($value);
			$temp=array();
			//echo mime_content_type($value) . "\n";
			/* get files content after processing  */
			$content=$wordReader->readTable($value,false,array(),true,array('','<br>'));
			//var_dump($content);
			//echo "<pre>"; print_r($content);
			/* Update DB */
			if($content!='')
			{

				$dynamicUpdate=array();
				$temp=array();
				foreach ($content as $key2 => $value2) {

					if(in_array($key2,$dynamicKeys) && !empty($value2)){

						$dynamicUpdate[$key2]=$value2[2];
					}
				}
				//echo "<pre>"; print_r($dynamicUpdate);
				$article=array();
				if(!empty($dynamicUpdate)){
					$article=$this->model->where('aui_article_id',strip_tags($content[0][2]))->where('aui_article_status',0)->first();
				}
				//dd($article);
				//echo "<pre>"; print_r($article);

				if($article && !empty($article))
				{
					$article->aui_dynamic_data=json_encode($dynamicUpdate);
					$article->aui_article_stage=1;
					$article->aui_project_id = $seton_config_id;
					$article->save();

					/* Create Xlsx Array */
					$staticData=json_decode($article->aui_static_data);
					$updatedStaticData=array();
					$staticData=(array) $staticData;

					//echo "<pre>"; print_r($staticData);exit;
					foreach ($staticData as $key => $value) {
						$value=(is_string( $value)) ? $value:'';
						$value=$this->edsLib->correctUnicode($value);
		           		$value=$this->edsLib->unicodeToAscii($value);
		          		$updatedStaticData[$key]= stripslashes(html_entity_decode($value));
					}
					$staticData=$updatedStaticData;
					//echo "<pre>"; print_r($staticData);
					//echo "<pre>"; print_r($dynamicUpdate);
					try
					{
						$temp = $this->createDeliveryOutput($type,$staticData,$dynamicUpdate,$seton_config_id);
					}catch(Exception $e)
					{
						//echo "Incorrect format!";
						//exit;
						return back()->with('errors',array('Incorrect format!'));
					}

				}
				else
				{
						//echo "Incorrect format!";
						//exit;
					\Session::flash('message','Article not found!');

					return redirect()->back();
				}

			}else{
						//echo "Articles not found";
						//exit;
					\Session::flash('message','Articles not found');
					return redirect()->back();
			}
			//dd($article);
			$temp[0] = $article->aui_article_id;
			$temp[] = $temp[6];
			$temp[] = $temp[7];
			$temp[] = $temp[9].'<bullet><Web target="#'.strip_tags($temp[10]).'">'.$temp[11].'</Web></bullet>'.
					  '<bullet><Web target="#'.strip_tags($temp[13]).'">'.$temp[14].'</Web></bullet>';
			$temp[] = '<h2><Anchor_Tag target="'.strip_tags($temp[10]).'">'.strip_tags($temp[11]).'</Anchor_Tag></h2>'.
					  $temp[12].'<h2><Anchor_Tag target=" '.strip_tags($temp[13]).'">'.$temp[14].'</Anchor_Tag></h2>'.$temp[15];

			unset($temp[6]);
			unset($temp[7]);

			ksort($temp);
			//echo "<pre>"; print_r($temp);
			$xlsxData[]=$temp;
			//dd($temp);
		}
		//dd($xlsxData);
		$xlsxData[0] = $headers;
		//dd($xlsxData);
		/* Get Write Path */
		$basicLibr = new BasicLib();
      	$fileName = $basicLibr->normaliseUrlString("Delivery_".$configuration->name."_".($type+1)."-".uniqid() ."_".rand(00,99).".xlsx" );
    	$filePath = public_path().'/uploads/products/'.$product.'/gen/' ;
    	$filePathUploads = '/products/'.$product.'/gen/' ;



      	$this->excellibobj->writeExcel($xlsxData,$filePath.$fileName);
      	/* Download entry Prepaarations and Entry*/

		/* Create Xlsx File Done   */
		$itemData = CepItems::leftjoin('cep_product_users','item_product_id','=','puser_product_id')
							->leftjoin('users','puser_user_id','=','id')
							->leftjoin('cep_groups','cep_groups.group_id','=','users.group_id')
							->where('item_id',$itemId)
							->where('group_code','CL')
							->select('id','item_product_id','item_id')
							->first();
		$name=uniqid();
		$fileInfo=pathinfo($fileName);
		$options =array (
	 				'name'=>$fileInfo['filename'],
	 				'file'=>basename($fileName),
	 				'type'=>'pre_delivery',
					'description'=> 'pre delviery upload ',
					'url'=> $filePathUploads,
					'client'=> $itemData->id,
					'product'=> $itemData->item_product_id,
					'item'=> $itemData->item_id,
					'reference_column'=> '',
      				'status'=> 1
					 );

		$upload=$this->FileManager->uploadEntry($options);
		return ;
    }

    public function memsourceUpload($id)
    {
        $prodConfigs=array();
        $prodConfigs['custom']=CepProductConfigurations::where('pconf_type','=','custom')
            ->where('pconf_product_id','=',$id)
            ->first();
        return view("clients_custom.epinternal.memsource_upload", compact( 'prodConfigs'));
    }

    public function memsource($id)
    {
        $prodConfigs=array();
	  	$productInfo=$this->productHelper->checkProductExists($id);

        return view("clients_custom.epinternal.memsource", compact( 'productInfo'));
    }

    /**
    * Date: 14-11-2017.
    * Process the XML file and add the missing CDATA into the output file.
    */
    public function memsourceProcess($id)
    {
    	$data = Request::all();
    	$parser = xml_parser_create();
    	//$inputData = json_decode(json_encode(simplexml_load_file(Input::file('file_input')->getRealPath(),'SimpleXMLElement',LIBXML_NOCDATA)),true);
    	//dd($data);
    	$inputData = file_get_contents(Input::file('file_input')->getRealPath());
        $inputData = preg_replace('/(<target xml:lang="en">)/', "<target xml:lang=\"en\"><![CDATA[", $inputData);
        $inputData = preg_replace('/(<\/target>)/', "]]></target>", $inputData);
    	//dd($inputData);
    	//dd($inputData['file']['body']);
    	// foreach($inputData['file']['body'] as $data)
    	// {
    	// 	foreach($data as $tKey=>$transData)
    	// 	{
    	// 		// foreach($transData as $gKey=>$groupData)
    	// 		// {
    	// 			//dd($groupData);
    	// 			foreach($transData['trans-unit'] as $key=>$transUnit)
    	// 			{
    	// 				$transData['trans-unit'][$key]['source'] = '<![CDATA['.$transUnit['source'].']]';
    	// 				if(empty($transUnit['target']))
    	// 				{
    	// 					unset($transUnit['target']);
    	// 					$transData['trans-unit'][$key]['target'] = '<![CDATA[ ]]';
    	// 				}
    	// 				else
    	// 				{
    	// 					$transData['trans-unit'][$key]['target'] = '<![CDATA['.$transUnit['target'].']]';
    	// 				}
    	// 			}
    	// 			//unset($data[$tKey]);
    	// 			$data[$tKey] = $transData;
    	// 		//}
     // 		}
     // 		$inputData['file']['body']['group'] = $data;
    	// }
    	// $xml = $this->array_to_xml($inputData,new \SimpleXMLElement('<root/>'));

		if (!file_exists(public_path().'/uploads/products/'.$id.'/memsource/'))
		{
		    mkdir(public_path().'/uploads/products/'.$id.'/memsource/', 0777, true);
		}
    	$file= public_path().'/uploads/products/'.$id.'/memsource/'.$data['file_templates_input'];
    	//dd(implode($inputData));
    	File::put($file,html_entity_decode($inputData));

    	return response()->download($file);
    	//dd($xml->asXMl());
    }//End of memourceUploadProcess

	function array_to_xml(array $arr, \SimpleXMLElement $xml)
	{
	    foreach ($arr as $k => $v) {
	    	if(is_array($v))
	    	{
	    		$this->array_to_xml($v, $xml->addChild($k));
	    	}
	    	else
	    	{
	    		if(!is_numeric($k))
	    		{
	    			$xml->addChild($k, $v);
	    		}
	    	}
	    }
	    return $xml;
	}

	/**
	* Date : 24-11-2017
	*
	*/

	public function loadRef($id)
	{

	}

	public function convertXlsToXlsx($id)
	{
		return view('clients_custom.epinternal.xlsToXlsx',['product_id'=>$id]);
	}//End of convertXlsToXlsx

	public function convertXlsToXlsxProcess($id)
	{
		$data = Request::all();
		$oldfile = $file=Input::file('file');

        //print_r($file);
		$item = CepItems::where('item_product_id',$id)->first();
		//dd($data['products']);
    	$filepath = "";
		$itemData = CepItems::leftjoin('cep_product_users','item_product_id','=','puser_product_id')
							->leftjoin('users','puser_user_id','=','id')
							->leftjoin('cep_groups','cep_groups.group_id','=','users.group_id')
							->where('item_id',$item->item_id)
							->where('group_code','CL')
							->select('id','item_product_id','item_id')
							->first();
		$upload=array();
		if($file)
		{
	        $name=uniqid();
			$options =array (
		 				'name'=>$name,
		 				'type'=>'custom',
						'description'=> 'custom upload',
						'url'=> 'products/'.$itemData->item_product_id.'/gen/inputs/',
						'client'=> $itemData->id,
						'product'=> $itemData->item_product_id,
						'item'=> $itemData->item_id,
						'reference_column'=> '',
          				'status'=>  1
						 );
			$upload=$this->FileManager->upload($file,$options);
		}
		$file=public_path()."/uploads/".$upload->upload_url;
		$pathInfo=pathinfo($file);
		$destFolder=public_path().'/uploads/products/'.$id.'/gen/inputs/'.$pathInfo['filename'];
		//echo "<pre>"; print_r($pathInfo);
		//echo "<pre>"; print_r($destFolder);exit;
		$wordReader=new WordReader();
		//$model=new CepClientLeparisianArticles();
		$basicLib=new basicLib();
		/* Get all files unzipped */
		$xlsFiles=$basicLib->unzip($file,$destFolder);
		$newFile = array();
		if(!empty($xlsFiles))
		{
			if(!is_dir(public_path().'/uploads/products/'.$id.'/xlstoxlsx/xlsx'))
			{
				mkdir(public_path().'/uploads/products/'.$id.'/xlstoxlsx/',0777);
				mkdir(public_path().'/uploads/products/'.$id.'/xlstoxlsx/xlsx',0777);
			}
			foreach($xlsFiles as $xlsFile)
			{
				$this->excellibobj->convertToXlsx($xlsFile,public_path().'/uploads/products/'.$id.'/xlstoxlsx/xlsx/'.basename($xlsFile,'.xls').'.xlsx');
				$newFile[] = public_path().'/uploads/products/'.$id.'/xlstoxlsx/xlsx/'.basename($xlsFile,'.xls').'.xlsx';
			}
		}

		if(!empty($newFile))
		{
			$zip = $this->zipLib->zipByNames(public_path().'/uploads/products/'.$id.'/xlstoxlsx/xlsx/'.basename($oldfile->getClientOriginalName(),'.zip'),$newFile);
		}

		return \Response::download($zip);
	}//End of convertXlsToXlsx

  public function listAll()
  {
    return view('clients_custom.epinternal.listall',compact('configurations'));
  }//End of listall

  public function loadResultSet()
  {
    $data = Request::all();
    $configurations = CepClientTemplateConfigModel::select('id','name','column_count','status','created_at','updated_at');
    if(isset($data['search']))
    {
      $configurations = $configurations->where('name','LIKE','%'.$data['search']['value'].'%');
    }
    $configurations = $configurations->skip($data['start'])->take($data['length'])->get()->toArray();
    $count = CepClientTemplateConfigModel::count();
    $output['draw'] = $data['draw'];
    $output['recordsTotal'] = $count;
    $output['recordsFiltered'] = $count;
    $output['data'] = json_encode($configurations);
    $res = '[';
    foreach($configurations as $key=>$configuration)
    {
      if($key == count($configurations)-1)
      {
        $res = $res.'["'.$configuration['id'].'","'.$configuration['name'].'","'.$configuration['column_count'].'","'.$configuration['status'].'","'.$configuration['created_at'].'","'.$configuration['updated_at'].'"]';

      }
      else
      {
        $res = $res.'["'.$configuration['id'].'","'.$configuration['name'].'","'.$configuration['column_count'].'","'.$configuration['status'].'","'.$configuration['created_at'].'","'.$configuration['updated_at'].'"],';
      }
    }
    $res = $res.']';
    echo '{"draw":'.$output['draw'].',"recordsTotal":'.$output['recordsTotal'].',"recordsFiltered":'.$output['recordsFiltered'].',"data":'.$res.'}';
    exit;
    // dd($count);
    // $draw = $_GET['draw']+1;
    // echo '{"draw":'.$draw.',"recordsTotal":57,"recordsFiltered":57,"data":[["Charde","Marshall","Regional Director","San Francisco","16th Oct 08","$470,600"],["Colleen","Hurst","Javascript Developer","San Francisco","15th Sep 09","$205,500"],["Dai","Rios","Personnel Lead","Edinburgh","26th Sep 12","$217,500"],["Donna","Snider","Customer Support","New York","25th Jan 11","$112,000"],["Doris","Wilder","Sales Assistant","Sidney","20th Sep 10","$85,600"],["Finn","Camacho","Support Engineer","San Francisco","7th Jul 09","$87,500"],["Fiona","Green","Chief Operating Officer (COO)","San Francisco","11th Mar 10","$850,000"],["Garrett","Winters","Accountant","Tokyo","25th Jul 11","$170,750"],["Gavin","Joyce","Developer","Edinburgh","22nd Dec 10","$92,575"],["Gavin","Cortez","Team Leader","San Francisco","26th Oct 08","$235,500"]]}';
    // exit;
  }
}
