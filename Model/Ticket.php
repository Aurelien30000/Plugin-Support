<?php
App::uses('CakeEvent', 'Event');

class Ticket extends SupportAppModel {
	
	public function afterSave($created, $options = array()) {
		if($created) {
			// nouvel enregistrement
			$this->getEventManager()->dispatch(new CakeEvent('afterAddTicket', $this));
		} else {
			// on édite, donc il reviens résolu
			$this->getEventManager()->dispatch(new CakeEvent('afterResolveTicket', $this));
		}
	}

	public function afterDelete($cascade = true) {
		$this->getEventManager()->dispatch(new CakeEvent('afterDeleteTicket', $this));
	}

}