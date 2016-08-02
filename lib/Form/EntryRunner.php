<?php


namespace xepan\accounts;

class Form_EntryRunner extends \Form {
	function addField($type, $options = null, $caption = null, $attr = null, $spot=null)
    {

        $insert_into = $this->layout ?: $this;

        if (is_object($type) && $type instanceof AbstractView && !($type instanceof Form_Field)) {

            // using callback on a sub-view
            $insert_into = $type;
            list(,$type,$options,$caption,$attr)=func_get_args();

        }

        if ($options === null) {
            $options = $type;
            $type = 'Line';
        }

        if (is_array($options)) {
            $name = isset($options["name"]) ? $options["name"] : null;
        } else {
            $name = $options; // backward compatibility
        }
        $name = preg_replace('|[^a-z0-9-_]|i', '_', $name);

        if ($caption === null) {
            $caption = ucwords(str_replace('_', ' ', $name));
        }

        /* normalzie name and put name back in options array */
        $name = $this->app->normalizeName($name);
        if (is_array($options)){
            $options["name"] = $name;
        } else {
            $options = array('name' => $name);
        }

        switch (strtolower($type)) {
            case 'dropdown':     $class = 'DropDown';     break;
            case 'checkboxlist': $class = 'CheckboxList'; break;
            case 'hidden':       $class = 'Hidden';       break;
            case 'text':         $class = 'Text';         break;
            case 'line':         $class = 'Line';         break;
            case 'upload':       $class = 'Upload';       break;
            case 'radio':        $class = 'Radio';        break;
            case 'checkbox':     $class = 'Checkbox';     break;
            case 'password':     $class = 'Password';     break;
            case 'timepickr':    $class = 'TimePicker';   break;
            default:             $class = $type;
        }
        $class = $this->app->normalizeClassName($class, 'Form_Field');

        if ($insert_into === $this) {
            $template=$this->template->cloneRegion('form_line');
            $field = $this->add($class, $options, null, $template);
        } else {
            if ($insert_into->template->hasTag($name)) {
                $template=$this->template->cloneRegion('field_input');
                $options['show_input_only']=true;
                $field = $insert_into->add($class, $options, $name);
            } else {
                $template=$this->template->cloneRegion('form_line');
                $field = $insert_into->add($class, $options, $spot, $template);
            }

            // Keep Reference, for $form->getElement().
            $this->elements[$options['name']]=$field;
        }


        $field->setCaption($caption);
        $field->setForm($this);
        $field->template->trySet('field_type', strtolower($type));

        if($attr) {
            if($this->app->compat) {
                $field->setAttr($attr);
            }else{
                throw $this->exception('4th argument to addField is obsolete');
            }
        }

        return $field;
    }
}