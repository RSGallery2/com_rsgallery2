/**
 * @package     RSGallery2
 *
 *
 *
 * @subpackage  com_rsgallery2
 * @copyright   (C) 2016-2018 RSGallery2 Team
 * @license     http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @author      finnern
 * @since       4.4.2
 */

//--------------------------------------------------------------
//
//--------------------------------------------------------------

/**/
jQuery(document).ready(function ($) {

    $('#maintain_template').change(function () {

        var form = document.getElementById('adminForm');

        form.task.value = 'mainttemplates.changeTemplate';
        form.submit();

    });

    $("button[name='btnConfigPara']").click(function (event) {

        var form = document.getElementById('adminForm');
        form.task.value = 'mainttemplates.saveConfigParameter';

        form.submit();
    });

    $("button[name='btnConfigFile']").click(function (event) {

        //-- assign to control --------------

        var form = document.getElementById('adminForm');
        form.task.value = 'mainttemplates.saveParamsFile';

        form.submit();
    });
    /**/
    
    
    $("button[name='btnUserCssFile']").click(function (event) {
        
        //-- assign to control --------------
        
        var form = document.getElementById('adminForm');
        form.task.value = 'mainttemplates.saveUserCssFile';
        
        form.submit();
    });

});
