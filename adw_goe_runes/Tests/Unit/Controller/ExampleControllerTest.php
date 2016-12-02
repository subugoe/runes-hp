<?php
namespace ADWGOE\AdwGoeRunes\Tests\Unit\Controller;
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Jens Bahr <ju.bahr@isfas.uni-kiel.de>, University of Kiel
 *  			
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * Test case for class ADWGOE\AdwGoeRunes\Controller\ExampleController.
 *
 * @author Jens Bahr <ju.bahr@isfas.uni-kiel.de>
 */
class ExampleControllerTest extends \TYPO3\CMS\Core\Tests\UnitTestCase
{

	/**
	 * @var \ADWGOE\AdwGoeRunes\Controller\ExampleController
	 */
	protected $subject = NULL;

	public function setUp()
	{
		$this->subject = $this->getMock('ADWGOE\\AdwGoeRunes\\Controller\\ExampleController', array('redirect', 'forward', 'addFlashMessage'), array(), '', FALSE);
	}

	public function tearDown()
	{
		unset($this->subject);
	}

	/**
	 * @test
	 */
	public function listActionFetchesAllExamplesFromRepositoryAndAssignsThemToView()
	{

		$allExamples = $this->getMock('TYPO3\\CMS\\Extbase\\Persistence\\ObjectStorage', array(), array(), '', FALSE);

		$exampleRepository = $this->getMock('', array('findAll'), array(), '', FALSE);
		$exampleRepository->expects($this->once())->method('findAll')->will($this->returnValue($allExamples));
		$this->inject($this->subject, 'exampleRepository', $exampleRepository);

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$view->expects($this->once())->method('assign')->with('examples', $allExamples);
		$this->inject($this->subject, 'view', $view);

		$this->subject->listAction();
	}

	/**
	 * @test
	 */
	public function showActionAssignsTheGivenExampleToView()
	{
		$example = new \ADWGOE\AdwGoeRunes\Domain\Model\Example();

		$view = $this->getMock('TYPO3\\CMS\\Extbase\\Mvc\\View\\ViewInterface');
		$this->inject($this->subject, 'view', $view);
		$view->expects($this->once())->method('assign')->with('example', $example);

		$this->subject->showAction($example);
	}
}
