<?php

/**
 * @file
 * The PHP page that serves all page requests on a Drupal installation.
 *
 * The routines here dispatch control to the appropriate handler, which then
 * prints the appropriate page.
 *
 * All Drupal code is released under the GNU General Public License.
 * See COPYRIGHT.txt and LICENSE.txt.
 */
//file_put_contents('/tmp/gyiua', print_r( $_SERVER, true ) );
//print_r( $_POST );die();
/**
 * Root directory of Drupal installation.
 */
define('DRUPAL_ROOT', getcwd());
//echo '<pre>';print_r($_SERVER);die();
require_once DRUPAL_ROOT . '/includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);
menu_execute_active_handler();

