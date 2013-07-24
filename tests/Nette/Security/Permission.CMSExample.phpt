<?php

/**
 * Test: Nette\Security\Permission Ensures that an example for a content management system is operable.
 *
 * @author     David Grudl
 * @package    Nette\Security
 */

use Nette\Security\Permission;


require __DIR__ . '/../bootstrap.php';


$acl = new Permission;
$acl->addRole('guest');
$acl->addRole('staff', 'guest');  // staff inherits permissions from guest
$acl->addRole('editor', 'staff'); // editor inherits permissions from staff
$acl->addRole('administrator');

// Guest may only view content
$acl->allow('guest', NULL, 'view');

// Staff inherits view privilege from guest, but also needs additional privileges
$acl->allow('staff', NULL, array('edit', 'submit', 'revise'));

// Editor inherits view, edit, submit, and revise privileges, but also needs additional privileges
$acl->allow('editor', NULL, array('publish', 'archive', 'delete'));

// Administrator inherits nothing but is allowed all privileges
$acl->allow('administrator');

// Access control checks based on above permission sets

Assert::true( $acl->isAllowed('guest', NULL, 'view') );
Assert::false( $acl->isAllowed('guest', NULL, 'edit') );
Assert::false( $acl->isAllowed('guest', NULL, 'submit') );
Assert::false( $acl->isAllowed('guest', NULL, 'revise') );
Assert::false( $acl->isAllowed('guest', NULL, 'publish') );
Assert::false( $acl->isAllowed('guest', NULL, 'archive') );
Assert::false( $acl->isAllowed('guest', NULL, 'delete') );
Assert::false( $acl->isAllowed('guest', NULL, 'unknown') );
Assert::false( $acl->isAllowed('guest') );

Assert::true( $acl->isAllowed('staff', NULL, 'view') );
Assert::true( $acl->isAllowed('staff', NULL, 'edit') );
Assert::true( $acl->isAllowed('staff', NULL, 'submit') );
Assert::true( $acl->isAllowed('staff', NULL, 'revise') );
Assert::false( $acl->isAllowed('staff', NULL, 'publish') );
Assert::false( $acl->isAllowed('staff', NULL, 'archive') );
Assert::false( $acl->isAllowed('staff', NULL, 'delete') );
Assert::false( $acl->isAllowed('staff', NULL, 'unknown') );
Assert::false( $acl->isAllowed('staff') );

Assert::true( $acl->isAllowed('editor', NULL, 'view') );
Assert::true( $acl->isAllowed('editor', NULL, 'edit') );
Assert::true( $acl->isAllowed('editor', NULL, 'submit') );
Assert::true( $acl->isAllowed('editor', NULL, 'revise') );
Assert::true( $acl->isAllowed('editor', NULL, 'publish') );
Assert::true( $acl->isAllowed('editor', NULL, 'archive') );
Assert::true( $acl->isAllowed('editor', NULL, 'delete') );
Assert::false( $acl->isAllowed('editor', NULL, 'unknown') );
Assert::false( $acl->isAllowed('editor') );

Assert::true( $acl->isAllowed('administrator', NULL, 'view') );
Assert::true( $acl->isAllowed('administrator', NULL, 'edit') );
Assert::true( $acl->isAllowed('administrator', NULL, 'submit') );
Assert::true( $acl->isAllowed('administrator', NULL, 'revise') );
Assert::true( $acl->isAllowed('administrator', NULL, 'publish') );
Assert::true( $acl->isAllowed('administrator', NULL, 'archive') );
Assert::true( $acl->isAllowed('administrator', NULL, 'delete') );
Assert::true( $acl->isAllowed('administrator', NULL, 'unknown') );
Assert::true( $acl->isAllowed('administrator') );

// Some checks on specific areas, which inherit access controls from the root ACL node
$acl->addResource('newsletter');
$acl->addResource('pending', 'newsletter');
$acl->addResource('gallery');
$acl->addResource('profiles', 'gallery');
$acl->addResource('config');
$acl->addResource('hosts', 'config');
Assert::true( $acl->isAllowed('guest', 'pending', 'view') );
Assert::true( $acl->isAllowed('staff', 'profiles', 'revise') );
Assert::true( $acl->isAllowed('staff', 'pending', 'view') );
Assert::true( $acl->isAllowed('staff', 'pending', 'edit') );
Assert::false( $acl->isAllowed('staff', 'pending', 'publish') );
Assert::false( $acl->isAllowed('staff', 'pending') );
Assert::false( $acl->isAllowed('editor', 'hosts', 'unknown') );
Assert::true( $acl->isAllowed('administrator', 'pending') );

// Add a new group, marketing, which bases its permissions on staff
$acl->addRole('marketing', 'staff');

// Refine the privilege sets for more specific needs

// Allow marketing to publish and archive newsletters
$acl->allow('marketing', 'newsletter', array('publish', 'archive'));

// Allow marketing to publish and archive latest news
$acl->addResource('news');
$acl->addResource('latest', 'news');
$acl->allow('marketing', 'latest', array('publish', 'archive'));

// Deny staff (and marketing, by inheritance) rights to revise latest news
$acl->deny('staff', 'latest', 'revise');

// Deny everyone access to archive news announcements
$acl->addResource('announcement', 'news');
$acl->deny(NULL, 'announcement', 'archive');

// Access control checks for the above refined permission sets

Assert::true( $acl->isAllowed('marketing', NULL, 'view') );
Assert::true( $acl->isAllowed('marketing', NULL, 'edit') );
Assert::true( $acl->isAllowed('marketing', NULL, 'submit') );
Assert::true( $acl->isAllowed('marketing', NULL, 'revise') );
Assert::false( $acl->isAllowed('marketing', NULL, 'publish') );
Assert::false( $acl->isAllowed('marketing', NULL, 'archive') );
Assert::false( $acl->isAllowed('marketing', NULL, 'delete') );
Assert::false( $acl->isAllowed('marketing', NULL, 'unknown') );
Assert::false( $acl->isAllowed('marketing') );

Assert::true( $acl->isAllowed('marketing', 'newsletter', 'publish') );
Assert::false( $acl->isAllowed('staff', 'pending', 'publish') );
Assert::true( $acl->isAllowed('marketing', 'pending', 'publish') );
Assert::true( $acl->isAllowed('marketing', 'newsletter', 'archive') );
Assert::false( $acl->isAllowed('marketing', 'newsletter', 'delete') );
Assert::false( $acl->isAllowed('marketing', 'newsletter') );

Assert::true( $acl->isAllowed('marketing', 'latest', 'publish') );
Assert::true( $acl->isAllowed('marketing', 'latest', 'archive') );
Assert::false( $acl->isAllowed('marketing', 'latest', 'delete') );
Assert::false( $acl->isAllowed('marketing', 'latest', 'revise') );
Assert::false( $acl->isAllowed('marketing', 'latest') );

Assert::false( $acl->isAllowed('marketing', 'announcement', 'archive') );
Assert::false( $acl->isAllowed('staff', 'announcement', 'archive') );
Assert::false( $acl->isAllowed('administrator', 'announcement', 'archive') );

Assert::false( $acl->isAllowed('staff', 'latest', 'publish') );
Assert::false( $acl->isAllowed('editor', 'announcement', 'archive') );

// Remove some previous permission specifications

// Marketing can no longer publish and archive newsletters
$acl->removeAllow('marketing', 'newsletter', array('publish', 'archive'));

// Marketing can no longer archive the latest news
$acl->removeAllow('marketing', 'latest', 'archive');

// Now staff (and marketing, by inheritance) may revise latest news
$acl->removeDeny('staff', 'latest', 'revise');

// Access control checks for the above refinements

Assert::false( $acl->isAllowed('marketing', 'newsletter', 'publish') );
Assert::false( $acl->isAllowed('marketing', 'newsletter', 'archive') );

Assert::false( $acl->isAllowed('marketing', 'latest', 'archive') );

Assert::true( $acl->isAllowed('staff', 'latest', 'revise') );
Assert::true( $acl->isAllowed('marketing', 'latest', 'revise') );

// Grant marketing all permissions on the latest news
$acl->allow('marketing', 'latest');

// Access control checks for the above refinement
Assert::true( $acl->isAllowed('marketing', 'latest', 'archive') );
Assert::true( $acl->isAllowed('marketing', 'latest', 'publish') );
Assert::true( $acl->isAllowed('marketing', 'latest', 'edit') );
Assert::true( $acl->isAllowed('marketing', 'latest') );
