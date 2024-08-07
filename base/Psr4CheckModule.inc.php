<?php
declare(strict_types=1);
// /vendor/slim4-mod/base/Psr4CheckModule.inc.php

use Slim\App;

/**
 * detect and route module for bootstrap change alias
 * return 1: arrayForAutoRoute, 2: $c->set('routeCurrentModule', hasUrlModule), 3: Void-setup-Route-Module-assets
 */
return  function (&$containerBuilder, &$psr4PathHelper)
	{
		
		$sysSettings = require_once __DIR__ . "\\Psr4SysSettingsAppend.php";
		
		$cbFake = null;
		$psr4PathHelper->prependPrs4Alias(null, $cbFake, $sysSettings, false); //lenh nay nap sau settings
		
		
		$gModule = '';
		$containerBuilder->addDefinitions([
			'routeCurrentModule' => '',
			'gBasePath' => null
		]);
		
		//$m = '';
		$m = null;
		
		$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));	
		$uri = (string) parse_url('http://a' . $_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);	
		if ($scriptDir !== '/' && stripos($uri, $_SERVER['SCRIPT_NAME']) === 0) {
			$seg = substr($uri,strlen($scriptDir));
			$containerBuilder->addDefinitions([
				'gBasePath' => $scriptDir
			]);
			//$scriptDir = null;
		} else {
			$seg = $uri;
		}
		
		$seg = strtolower(ltrim(str_replace('/index.php','',$seg),'/'));
		
		$u = explode('/',$seg);

		$d = '';
		$aliasAsset = '';
		$mkGModule = function() use (&$gModule, &$u, &$m, &$containerBuilder, &$psr4PathHelper, &$d) {
			if (!isset($u[1])) $u[] = ''; //['','',''];
			$gModule = '/' . $u[0];
			$m = $gModule . '/' . $u[1];
			$containerBuilder->addDefinitions([
				'routeCurrentModule' => $m
			]);
			$psr4PathHelper->addPsr4('App\\', $d, true);
			$u = array_slice($u,1);
		};
		$dApp = $psr4PathHelper->aliasToFull('App\\', true);
		if (empty($u[0])) {
			$d = $dApp;
		} else {
			$d = $psr4PathHelper->aliasToFull($u[0]. '\\');
			if ($d==$u[0].'\\') { //khong co alias, chi co the la extBasePath hoac thu muc "user/controllers"
				$hasController = false;
				for ($i = 0; $i < count($u); $i++) {
					if (empty($u[$i])) break;
					$d = $psr4PathHelper->aliasToFull($u[$i] . '\\');
					if ($d == $u[$i] . "\\") {
						if (is_dir($dApp . "controllers\\" . $u[$i])) {
							$uri = "/" . implode("/", array_slice($u,0,$i+1));
							$aliasAsset = "/" . implode("/", array_slice($u,0,$i));
							$u = array_slice($u, $i);
							$d = $dApp;
							$hasController = true;
							break;
						}
					} else if (is_dir($d. 'controllers')) {
						$hasController = true;
						$uri = "/" . implode("/", array_slice($u,0,$i+2));
						$aliasAsset = "/" . implode("/", array_slice($u,0,$i));
						$u = array_slice($u,$i);
						$mkGModule();
						break;
					}
				}
				if (!$hasController) {
					$u = ['','','_NoController'];
					$d = $dApp;
					$aliasAsset = $uri;
				}
			} else { //co alias
				$mkGModule();
			}
		}
		array_push($u,'','');
		$uri = rtrim($uri,'/');
		$aliasAsset = rtrim($aliasAsset,'/');
		
		return ['u' => $u, 'gModule' => $gModule, 'd' => str_replace('\\','/', $d . 'web/assets/'), 'm' => $m, 'urlPath' => $uri, 'wPath' => $d, 'aliasAsset' => $aliasAsset];
	}
;