<?php
class CreateNewForm extends WikiAttachmentsForm {

	function set_up(){
		$this->add_field("file", new FileField([
			"label" => _("Soubor"),
			"max_size" => 1024 * 1024 * 100,
		]));

		$this->add_field("replace_existing", new BooleanField([
			"label" => _("Nahradit přílohu se stejným názvem"),
			"required" => false,
		]));

		$this->set_button_text(_("Nahrát přílohu"));
	}
}
