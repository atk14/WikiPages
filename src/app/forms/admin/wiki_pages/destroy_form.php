<?php
class DestroyForm extends AdminForm {

	function set_up(){
		$this->add_field("confirmation", new ConfirmationField([
			"label" => _("I do confirm the page deletion"),
		]));
	}
}
