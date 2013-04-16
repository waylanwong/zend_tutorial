<?php
namespace Album\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Album\Model\Album;
use Album\Form\AlbumForm;

class AlbumController extends AbstractActionController {
    
    protected $albumTable;
    
	public function indexAction() {
		return new ViewModel(array(
		  'albums' => $this->getAlbumTable()->fetchAll(),
        ));
	}
	
	public function addAction() {
		    
        //echo "DEBUG: add action called <br />";
        
		$form = new AlbumForm();
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        
        if ($request->isPost()) {
            //echo "DEBUG: Post data is present <br />";
                
            $album = new Album();
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                
                //echo "DEBUG: form data is valid <br />";
                
                $album->exchangeArray($form->getData());
                $this->getAlbumTable()->saveAlbum($album);
                
                // Redirect to the list of albums                
                return $this->redirect()->toRoute('album');
            }
        }
        
        //echo "DEBUG: just returning the form as an object <br />";
        return array('form' => $form);
	}
	
	public function editAction() {
	    
		$id = (int) $this->params()->fromRoute('id', 0);
        if (!$id) {
            // cannot find an id, redirecting to add an album instead
            return $this->redirect()->toRoute('album', array(
                'action' => 'add'
            ));            
        }
        $album = $this->getAlbumTable()->getAlbum($id);
        
        $form = new AlbumForm();
        $form->bind($album);
        $form->get('submit')->setAttribute('value', 'Edit');
        
        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->setInputFilter($album->getInputFilter());
            $form->setData($request->getPost());
            
            if ($form->isValid()) {
                $this->getAlbumTable()->saveAlbum($form->getData());
                
                // Redirect to list of albums
                return $this->redirect()->toRoute('album');
            }
        }
        // no valid data found, return the form for the viewer.
        return array('id' => $id, 'form' => $form);
	}
	
	public function deleteAction() {
	    echo "DEBUG: Received delete request, waiting for confirmation <br />";
        
		$id = (int) $this->params()->fromRoute('id', 0);
        
        if (!$id) {
            
            echo "DEBUG: Invalid ID during delete request! <br />";
            
            return $this->redirect()->toRoute('album');
        }
        $request = $this->getRequest();
        
        if ($request->isPost()) {            
            
            echo "DEBUG: Delete request passed post test. <br />";
                
            $del = $request->getPost('del', 'No'); // the second parameter is the default value returned if the POST key is not valid
            
            if ($del == 'Yes') {
                
                echo "DEBUG: Received confirmed delete request. Calling deleteAlbum() <br />";
                
                $id = (int)$request->getPost('id');
                $this->getAlbumTable()->deleteAlbum($id);
            }
            echo "DEBUG: Redirecting back to album list <br />";
            // Redirect to list of albums
            return $this->redirect()->toRoute('album');
        }
        
        return array(
            'id' => $id,
            'album' => $this->getAlbumTable()->getAlbum($id)
        );
        
	}
    
    public function getAlbumTable() {
        if (!$this->albumTable) {
            $sm = $this->getServiceLocator();
            $this->albumTable = $sm->get('Album\Model\AlbumTable');
        }
        return $this->albumTable;
    }
};

