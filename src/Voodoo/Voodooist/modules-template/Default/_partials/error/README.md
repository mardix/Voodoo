Error contains the error pages to show

From the controller you set it this way:

$this->view()->setError("something bad happen", 500);