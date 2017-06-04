<?php
namespace WPUtil\Vendor;

class GravityForms {
	private static $_form_choices;
	
    public static function get_all_forms() {
		if (!self::$_form_choices) {
			$form_choices = array();
			
            if (method_exists('RGFormsModel','get_forms')) {
			    $forms = \RGFormsModel::get_forms(null, 'title');
			    
                foreach($forms as $form) {
			        $form_choices[$form->id] = $form->title;
			    }
			}

			self::$_form_choices = $form_choices;
		}
        
		return self::$_form_choices;
	}
}