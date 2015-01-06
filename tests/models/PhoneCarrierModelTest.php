<?php

/**
 * @group Model
 */
class PhoneCarrierModelTest extends CIUnit_TestCase
{
	protected $tables = array(
		'phone_carrier' => 'phone_carrier'
	);

	private $_pcm;

	public function __contruct($name = NULL, array $data = array(), $dataName = '')
	{
		parent::__construct($name, $data, $dataName);

	}

	public function setUp()
	{

		$this->tearDown();
		
		//migrationでテーブル作成
		//protected $tablesでphone_carrierテーブルに対するfixtureが実行されるため、parent::setUp()よりも先に実行する。
		$this->CI->load->library('migration');
		$this->CI->migration->current();

		parent::setUp();
		//テストするmodelをload
		$this->CI->load->model('phone_carrier_model');
		$this->_pcm = $this->CI->phone_carrier_model;
		//fixtures利用 protected $tablesの設定を書かずにfixtureを使う場合。
		//$this->dbfixt(array('phone_carrier' => 'phone_carrier'));
	}

	public function tearDown()
	{
		parent::tearDown();
	}

	// ------------------------------------------------------------------------

	/**
	 * @dataProvider testGetCarriersData
	 */
	public function testGetCarriers(array $attributes, $expected)
	{
		$actual = $this->_pcm->getCarriers($attributes);
		$this->assertEquals($expected, count($actual));
	}

	public function testGetCarriersData()
	{
		return array(
			array(array('name'), 5)
		);
	}

	// ------------------------------------------------------------------------
}