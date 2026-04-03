<?php
class Views{
    function getView($controller, $view, $data="", $alert="", $config = "", $cliente = "")
    {
        $controller = get_class($controller);
        if ($controller == "Home") {
            $view = "Views/".$view.".php";
        }else{
            $view = "Views/" . strtolower($controller) . "/" . $view . ".php";
        }
        require_once($view);
    }
}

?>
