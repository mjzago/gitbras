<?php

/*
 * This file is part of OPUS. The software OPUS has been originally developed
 * at the University of Stuttgart with funding from the German Research Net,
 * the Federal Department of Higher Education and Research and the Ministry
 * of Science, Research and the Arts of the State of Baden-Wuerttemberg.
 *
 * OPUS 4 is a complete rewrite of the original OPUS software and was developed
 * by the Stuttgart University Library, the Library Service Center
 * Baden-Wuerttemberg, the Cooperative Library Network Berlin-Brandenburg,
 * the Saarland University and State Library, the Saxon State Library -
 * Dresden State and University Library, the Bielefeld University Library and
 * the University Library of Hamburg University of Technology with funding from
 * the German Research Foundation and the European Regional Development Fund.
 *
 * LICENCE
 * OPUS is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or any later version.
 * OPUS is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU General Public License for more
 * details. You should have received a copy of the GNU General Public License
 * along with OPUS; if not, write to the Free Software Foundation, Inc., 51
 * Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * @category    Application Unit Test
 * @author      Michael Lang <lang@zib.de>
 * @copyright   Copyright (c) 2008-2019, OPUS 4 development team
 * @license     http://www.gnu.org/licenses/gpl.html General Public License
 */

class ReviewerAndFullAdminTest extends ControllerTestCase
{

    protected $configModifiable = true;

    protected $additionalResources = ['authz', 'database', 'translation', 'view', 'mainMenu', 'navigation'];

    public function setUp()
    {
        parent::setUp();
        $this->enableSecurity();
        $this->loginUser('security4', 'security4pwd');
    }

    public function tearDown()
    {
        $this->logoutUser();
        $this->restoreSecuritySetting();
        parent::tearDown();
    }

    /**
     * Pr??ft, ob 'Review' Eintrag im Hauptmenu existiert.
     */
    public function testMainMenu()
    {
        $this->useEnglish();
        $this->dispatch('/home');
        $this->assertQueryContentContains("//div[@id='header']", 'Administration');
        $this->assertNotQueryContentContains("//div[@id='header']", 'Review');
    }

    /**
     * Pr??ft, da?? Review Link in Admin Menu erscheint.
     */
    public function testReviewAccessInAdminMenu()
    {
        $this->useEnglish();
        $this->dispatch('/admin');
        $this->assertQueryContentContains('//html/body', 'Review');
    }

    /**
     * Pr??ft, ob auf die Startseite des Review Modules zugegriffen werden kann.
     */
    public function testAccessReviewModule()
    {
        $this->useEnglish();
        $this->dispatch('/review');
        $this->assertQueryContentContains('//html/head/title', 'Review Documents');
    }

    /**
     * Pr??ft, das auf die Seite zur Verwaltung von Dokumenten zugegriffen werden kann.
     */
    public function testAccessDocumentsController()
    {
        $this->useEnglish();
        $this->dispatch('/admin/documents');
        $this->assertQueryContentContains('//html/head/title', 'Administration of Documents');
    }
}
