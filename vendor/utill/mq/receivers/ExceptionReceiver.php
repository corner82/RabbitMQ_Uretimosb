<?php
/**
 * OSTİM TEKNOLOJİ Framework 
 *
 * @link      https://github.com/corner82/RabbitMQ_SanalFabrika for the canonical source repository
 * @copyright Copyright (c) 2016 OSTİM TEKNOLOJİ (http://www.ostim.com.tr)
 * @license   
 */
namespace Utill\MQ\Receivers;
//require_once '..\vendor\autoload.php';

use PhpAmqpLib\Connection\AMQPConnection;
use PhpAmqpLib\Message\AMQPMessage;
 
class ExceptionReceiver
{
    /* ... SOME OTHER CODE HERE ... */
     
    /**
     * Process incoming request to generate pdf invoices and send them through 
     * email.
     */
    public function listen()
    {
        $connection = new AMQPConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();
         
        $channel->queue_declare(
            'invoice_queue',    #queue
            false,              #passive
            true,               #durable, make sure that RabbitMQ will never lose our queue if a crash occurs
            false,              #exclusive - queues may only be accessed by the current connection
            false               #auto delete - the queue is deleted when all consumers have finished using it
            );
             
        /**
         * don't dispatch a new message to a worker until it has processed and 
         * acknowledged the previous one. Instead, it will dispatch it to the 
         * next worker that is not still busy.
         */
        $channel->basic_qos(
            null,   #prefetch size - prefetch window size in octets, null meaning "no specific limit"
            1,      #prefetch count - prefetch window in terms of whole messages
            null    #global - global=null to mean that the QoS settings should apply per-consumer, global=true to mean that the QoS settings should apply per-channel
            );
         
        /**
         * indicate interest in consuming messages from a particular queue. When they do 
         * so, we say that they register a consumer or, simply put, subscribe to a queue.
         * Each consumer (subscription) has an identifier called a consumer tag
         */
        $channel->basic_consume(
            'exceptions_queue',     #queue
            '',                     #consumer tag - Identifier for the consumer, valid within the current channel. just string
            false,                  #no local - TRUE: the server will not send messages to the connection that published them
            false,                  #no ack, false - acks turned on, true - off.  send a proper acknowledgment from the worker, once we're done with a task
            false,                  #exclusive - queues may only be accessed by the current connection
            false,                  #no wait - TRUE: the server will not respond to the method. The client should not wait for a reply method
            array($this, 'process') #callback
            );
             
        while(count($channel->callbacks)) {
            //$this->log->addInfo('Waiting for incoming messages');
            $channel->wait();
        }
         
        $channel->close();
        $connection->close();
    }
     
    /**
     * process received request
     * 
     * @param AMQPMessage $msg
     */
    public function process(AMQPMessage $msg)
    {
        //$this->generatePdf()->sendEmail();
        $this->writeLog($msg);
         
        /**
         * If a consumer dies without sending an acknowledgement the AMQP broker 
         * will redeliver it to another consumer or, if none are available at the 
         * time, the broker will wait until at least one consumer is registered 
         * for the same queue before attempting redelivery
         */
        $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    }
    
    private function writeLog($message) {
        //print_r(json_decode($message->body));
        try {
            $messageBody = json_decode($message->body);
            print_r($messageBody->time);
            print_r($messageBody->logFormat);
            if(is_object($messageBody)) {
                if($messageBody->logFormat == 'file') {
                    try {
                        $file = fopen("../log/exceptions.txt","a"); 
                        fwrite($file,"Hata Dosyası    : ".$messageBody->file."\r\n");
                        fwrite($file,"Hata Satırı     : ".$messageBody->line."\r\n");
                        fwrite($file,"Hata Açıklaması : ".$messageBody->message."\r\n");
                        fwrite($file,"Zaman           : ".$messageBody->time."\r\n");
                        fwrite($file,"Serial          : ".$messageBody->serial."\r\n");
                        fwrite($file,"---------------------------------------------------\r\n");
                        fclose($file); 
                    } catch (Exception $exc) {
                        echo $exc->getTraceAsString();
                        mail('311corner82@gmail.com', 'rabbitMQ logging Exception', $exc->getTraceAsString());
                    } 
                }
            }
        } catch (Exception $exc) {
            echo $exc->getTraceAsString();
            mail('311corner82@gmail.com', 'rabbitMQ logging Exception', $exc->getTraceAsString());
        } 

        
        
    }


    /**
     * Generates invoice's pdf
     * 
     * @return WorkerReceiver
     */
    private function generatePdf()
    {
        /**
         * Mocking a pdf generation processing time.  This will take between
         * 2 and 5 seconds
         */
        sleep(mt_rand(2, 5));
        return $this;
    }
     
    /**
     * Sends email
     * 
     * @return WorkerReceiver
     */
    private function sendEmail()
    {
        /**
         * Mocking email sending time.  This will take between 1 and 3 seconds
         */
        sleep(mt_rand(1,3));
        return $this;
    }
}

//$test = new WorkerReceiver();
//$test->listen();