<?php
/**
 * This file is part of Webtemplate.
 *
 * (c) Sandy McNeil <g7mzrdev@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace webtemplate;

/**
 * Webtemplate Class AutoLoader
 *
 * @category Webtemplate
 * @package  Autoloader
 * @author   Sandy McNeil <g7mzrdev@gmail.com>
 * @license  View the license file distributed with this source code
 **/
class AutoLoader
{
    /**
     * Automatically loads classes require by webtemplate by converting the class
     * name to a filename and loading the file.  It also verifys the class is
     * present in the file.
     *
     * @param string $class The name of the class to be loaded
     *
     * @return boolan True if Class Loaded
     *
     * @access public
     */
    public static function loader($class)
    {
        //  Class or trait loaded
        $loadedOK = false;

        // Replace the Namespace Seperators with Directory Seperators
        $filename = str_replace('\\', '/', $class);

        // Remove the Top Level webtamplate Namespace
        $filename = str_replace('webtemplate', '', $filename);

        // Ensure filename is in lowwer case
        $filename = strtolower($filename);

        // Check if a class or trait is to be loaded
        if (strpos($filename, 'trait') !== false) {
            // It is a trait
            // Remove the trait flag
            $filename = str_replace('trait', '', $filename);

            // Add the current directory and extension to the filename
            $filename = __DIR__ . $filename . ".trait.php";

            // Check file exists
            if (file_exists($filename)) {
                include $filename;

                // Check the trait exists
                if (trait_exists($class)) {
                    $loadedOK = true;
                }
            }
        } elseif (strpos($filename, 'interface') !== false) {
            // It is an interface
            // Remove the trait flag
            $filename = str_replace('interface', '', $filename);

            // Add the current directory and extension to the filename
            $filename = __DIR__ . $filename . ".if.php";

            // Check file exists
            if (file_exists($filename)) {
                include $filename;

                // Check the trait exists
                if (trait_exists($class)) {
                    $loadedOK = true;
                }
            }
        } else {
            // It is a class
            // Add the Current Direcory and file extensions to the  Filename
            $filename = __DIR__ . $filename . ".class.php";

            //Check the file exists.  If it does load it.
            if (file_exists($filename)) {
                include $filename;

                // Check the class exists.  If it does return true
                if (class_exists($class)) {
                    $loadedOK = true;
                }
            }
        }

        // return False if the class or trait is not loaded.
        return $loadedOK;
    }
}
