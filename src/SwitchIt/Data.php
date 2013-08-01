<?php

namespace SwitchIt;

use Exception;

class Data
{
	private $dataFile = null;

	function __construct($dataFilePath)
	{
		if (!file_exists($dataFilePath))
			file_put_contents($dataFilePath, json_encode(array()));

		$this->dataFile = $dataFilePath;
	}


	/**
	 * get data from data-file
	 *
	 *
	 * @return mixed
	 */
	public function fetchData()
	{


		$aData = json_decode(file_get_contents($this->dataFile), true);

		if (isset($aData['groups']))
			asort($aData['groups']);
		else
			$aData['groups'] = array();

		$aData['aFilledGroups'] = $this->getFilledGroups($aData);

		if (!isset($aData['options']['locale']))
			$aData['options']['locale'] = 'de';

		if (!isset($aData['cronjobs']))
			$aData['cronjobs'] = array();

		asort($aData['cronjobs']);

		return $aData;
	}


	/**
	 * save data to data-file
	 *
	 *
	 * @param array $aData
	 *
	 * @return bool
	 */
	public function saveData(array $aData)
	{
		if (isset($aData['aFilledGroups']))
			unset($aData['aFilledGroups']);

		file_put_contents($this->dataFile, utf8_encode(json_encode($aData)));

		return true;
	}


	public function getFilledGroups(array $aData)
	{
		$aFilledGroups = array();
		
		if (isset($aData['groups']) && isset($aData['switches']))
		{
			foreach($aData['groups'] AS $kG => $group)
			{
				foreach($aData['switches'] AS $switch)
					if ($switch['group'] == $kG)
						$aFilledGroups[$kG] = $group;
			}
		}
		
		asort($aFilledGroups);

		return $aFilledGroups;
	}
	
}
