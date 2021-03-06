<?php

namespace Grocy\Controllers;

use \Grocy\Services\DatabaseService;
use \Grocy\Services\ApplicationService;

class SystemApiController extends BaseApiController
{
	public function __construct(\Slim\Container $container)
	{
		$fp = fopen('/config/data/sql.log', 'a');
                fwrite($fp, "#### Constructing SystemApiController ####\n");
                $time_start = microtime(true);
		parent::__construct($container);
		$this->DatabaseService = new DatabaseService();
		$this->ApplicationService = new ApplicationService();
                fwrite($fp, "-----Total execution time in seconds: " . round((microtime(true) - $time_start),4) . "\n");
                fwrite($fp, "#### SystemApiController constructed ####\n");
                fclose($fp);
	}

	protected $DatabaseService;
	protected $ApplicationService;

	public function GetDbChangedTime(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args)
	{
		$fp = fopen('/config/data/sql.log', 'a');
                fwrite($fp, "---- getting db changed time ----\n");
                $time_start = microtime(true);
		$response = $this->ApiResponse(array(
			'changed_time' => $this->DatabaseService->GetDbChangedTime()
		));
                fwrite($fp, "----Total execution time in seconds: " . round((microtime(true) - $time_start),4) . "\n");
                fwrite($fp, "---- time obtained ----\n");
                fclose($fp);
		return $response;
	}

	public function LogMissingLocalization(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args)
	{
		if (GROCY_MODE === 'dev')
		{
			try
			{
				$requestBody = $request->getParsedBody();

				$this->LocalizationService->CheckAndAddMissingTranslationToPot($requestBody['text']);
				return $this->EmptyApiResponse($response);
			}
			catch (\Exception $ex)
			{
				return $this->GenericErrorResponse($response, $ex->getMessage());
			}
		}	
	}

	public function GetSystemInfo(\Slim\Http\Request $request, \Slim\Http\Response $response, array $args)
	{
		return $this->ApiResponse($this->ApplicationService->GetSystemInfo());
	}
}
