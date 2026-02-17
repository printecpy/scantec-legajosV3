<?php
    class Api extends Controllers{
        public function __construct()
        {
            
            parent::__construct();

        }
   
    public function expediente()
    {
        $cant_expedient = $this->model->selectExpedienteCantidad();
        $data = ['cant_expedient'=> $cant_expedient];
        $this->views->getView($this, "expedientes", $data);
    }



}