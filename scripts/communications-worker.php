<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/app/bootstrap.php';
try{do{$r=comm_process_queue(10);echo json_encode($r)."\n";if(in_array('--once',$argv,true))break;sleep(5);}while(true);}catch(Throwable $e){fwrite(STDERR,"Communications worker failed; check private error logs and configuration.\n");exit(1);}
