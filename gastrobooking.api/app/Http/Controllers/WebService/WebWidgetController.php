<?php
namespace App\Http\Controllers\WebService;

use App\Http\Controllers\Controller;
use App\Http\Controllers\WebService\ServiceHelpers;
use App\Repositories\WebServiceRepository;
use Dingo\Api\Routing\Helpers;
use Illuminate\Http\Request;

/**
 * Class WebServiceController
 *
 * @package App\Http\Controllers\WebService
 */
class WebWidgetController extends Controller
{
    use Helpers;

    public function css(Request $request) {
        $selectors = [
            'menutype' => '.widget__menutype',
            'menugroup' => '.widget__menugroup',
            'menuofdaybutton' => '.widget__menuofdaybutton',
            'menusubgroup' => '.widget__menusubgroup',
            'menutitle' => '.widget__menutitle',
            'menulist' => '.widget__menulist',
            'menulistcomment' => '.widget__menulistcomment',
            'bookbutton' => '.widget__bookbutton',
            'datepicker' => '.widget__datepicker',
            'menulistorder' => '.widget__menulistorder',
            'headerchange'=>'.widget__headerchange',//header change


            'bgcolor' => ['html, body', 'background-color'],
            'headercolor' => ['.navbar-default .widget__headercolor>li>a,.widget__headercolor>li>select', 'color'],//---color of text
            'showphoto' => ['.widget__showphoto ',['off' => ['display' => 'none'], 'on' => ['display' => 'block']]],
            'listdetail' => ['.widget__menulistcomment',['off' => ['display'=>'none'], 'on' => ['display'=>'block']]],
            'menusubgroup_activation' => ['.widget__menusubgroup',['off'=>['display'=>'none'],'on'=>['display'=>'block']]],//subgroup activation
            'menulist_prefix_one' => ['.widget__prefix_one',['off'=>['display'=>'none'],'on'=>['display'=>'inline-block']]],//prefix 1 on/off
            'photo_half_size' => ['.widget__photosize',['off'=>['width'=>'80px','height'=>'60px'],'on'=>['width'=>'40px','height'=>'30px']]],
            'menulist_prefix_two' => ['.widget__prefix_two',['off'=>['display'=>'none'],'on'=>['display'=>'inline-block']]],//prefix 2 on/off
            'menulist_rectangle' => ['.widget__menutitle',['off'=>['margin-bottom'=>'0px'],'on'=>['margin-bottom'=>'20px']]],//padding size for list
            'show_order' => ['.widget__order',['off'=>['display'=>'none'],'on'=>['display'=>'block']]] //show/hide order window
            
        ];

        $webWidgetCSS = new ServiceHelpers\WebWidgetCssHelper();

        $css = $webWidgetCSS
            ->setSelectors($selectors)
            ->generate($_GET)
            ->getSelectors()
            ->get();

        return response($css)->withHeaders(['Content-Type' => 'text/css']);
    }
    
    public function js(Request $request) {
        $scripts = [
            /*'lang' => "$('header select option[value=\"@{lang}\"]').attr('selected', 'true').trigger('change');"*/

        ];

        $webWidgetJS = new ServiceHelpers\JSGenerator();

        $js = $webWidgetJS
            ->setScripts($scripts)
            ->generate($_GET)
            ->get();

        return response($js)->withHeaders(['Content-Type' => 'application/javascript']);
    }
}