<?php
class HomeController extends AppController {

    public function index() {
        $this->set('title_for_layout',"Support");
    	$this->layout = $this->Configuration->getKey('layout');
        $this->loadModel('Support.Ticket');
    	$tickets = $this->Ticket->find('all', array('order' => array('id' => 'desc')));
    	$this->set(compact('tickets'));
        $this->loadModel('Support.ReplyTicket');
        $reply_tickets = $this->ReplyTicket->find('all');
        $this->set(compact('reply_tickets'));
    	$nbr_tickets = $this->Ticket->find('count'); $this->set(compact('nbr_tickets'));
    	$nbr_tickets_resolved = $this->Ticket->find('count', array('conditions' => array('state' => 1))); $this->set(compact('nbr_tickets_resolved'));
    	$nbr_tickets_unresolved = $this->Ticket->find('count', array('conditions' => array('state' => 0))); $this->set(compact('nbr_tickets_unresolved'));
    }

    public function ajax_delete() {
    	$this->layout = null;
    	$this->loadModel('Support.Ticket');
        $pseudo = $this->Ticket->find('all', array('conditions' => array('id' => $this->request->data['id'])));
        $pseudo = $pseudo['0']['Ticket']['author'];
        if($this->isConnected AND $this->User->isAdmin() OR $this->isConnected AND $this->User->getKey('pseudo') == $pseudo AND $this->Permissions->can('DELETE_HIS_TICKET') OR $this->Permissions->can('DELETE_ALL_TICKETS')) {
    		$this->loadModel('Support.Ticket');
    		if($this->request->is('post')) {
    			$this->Ticket->delete($this->request->data['id']);
                $this->loadModel('Support.ReplyTicket');
                $this->ReplyTicket->deleteAll(array('ticket_id' => $this->request->data['id']));
    			echo 'true';
    		} else {
    			echo 'NOT_POST';
    		}
    	} else {
    		echo 'NOT_ADMIN_OR_CREATOR';
    	}
    }

    public function ajax_reply_delete() {
        $this->layout = null;
        if($this->isConnected AND $this->User->isAdmin()) {
            $this->loadModel('Support.ReplyTicket');
            if($this->request->is('post')) {
                $this->ReplyTicket->delete($this->request->data['id']);
                echo 'true';
            } else {
                echo 'NOT_POST';
            }
        } else {
            echo 'NOT_ADMIN';
        }
    }

    public function ajax_resolved() {
    	$this->layout = null;
    		if($this->request->is('post')) {
    			$this->loadModel('Support.Ticket');
		    	$pseudo = $this->Ticket->find('all', array('conditions' => array('id' => $this->request->data['id'])));
		    	$pseudo = $pseudo['0']['Ticket']['author'];
		    	if($this->isConnected AND $this->User->isAdmin() OR $this->isConnected AND $this->User->getKey('pseudo') == $pseudo AND $this->Permissions->can('RESOLVE_HIS_TICKET') OR $this->Permissions->can('RESOLVE_ALL_TICKETS')) {
					$this->Ticket->read(null, $this->request->data['id']);
					$this->Ticket->set(array('state' => 1));
					$this->Ticket->save();
					echo 'true';
		    	} else {
		    		echo 'NOT_PERMISSION';
		    	}
    	} else {
			echo 'NOT_POST';
		}
    }

    public function ajax_reply() {
        $this->layout = null;

            if($this->request->is('post')) {
                if(!empty($this->request->data['message']) && !empty($this->request->data['id'])) {
                    $this->loadModel('Support.Ticket');
                    $pseudo = $this->Ticket->find('all', array('conditions' => array('id' => $this->request->data['id'])));
                    $pseudo = $pseudo['0']['Ticket']['author'];
                    if($this->isConnected AND $this->User->isAdmin() OR $this->isConnected AND $this->User->getKey('pseudo') == $pseudo AND $this->Permissions->can('REPLY_TO_HIS_TICKETS') OR $this->Permissions->can('REPLY_TO_ALL_TICKETS')) {
                        $this->loadModel('Support.ReplyTicket');
                        $this->ReplyTicket->create();
                        $this->ReplyTicket->set(array('ticket_id' => $this->request->data['id'], 'reply' => $this->request->data['message'], 'author' => $this->User->getKey('pseudo')));
                        $this->ReplyTicket->save();
                        echo 'true';
                    } else {
                        echo 'NOT_PERMISSION';
                    }
                } else {
                    echo '1';
                }
        } else {
            echo 'NOT_POST';
        }
    }

    public function ajax_post() {
        $this->layout = null;

        if($this->request->is('post')) {
            if(!empty($this->request->data['title']) AND !empty($this->request->data['content'])) {
                if($this->isConnected AND $this->Permissions->can('POST_TICKET')) {
                    $this->loadModel('Support.Ticket');
                    $this->request->data['author'] = $this->User->getKey('pseudo');
                    $this->request->data['private'] = $this->request->data['ticket_private'];
                    $this->request->data['title'] == before_display($this->request->data['title']);
                    $this->request->data['content'] == before_display($this->request->data['content']);
                    $this->Ticket->read(null, null);
                    $this->Ticket->set($this->request->data);
                    $this->Ticket->save();
                    $id = $this->Ticket->find('all', array('conditions' => array('title' => $this->request->data['title'], 'content' => $this->request->data['content'], 'author' => $this->request->data['author'])));
                    $id = $id['0']['Ticket']['id'];
                    echo $id;
                } else {
                    echo 'NOT_PERMISSION';
                }
            } else {
                echo $this->Lang->get('ERROR__FILL_ALL_FIELDS');
            }
        } else {
            echo 'NOT_POST';
        }
    }
}
