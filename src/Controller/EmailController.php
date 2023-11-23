<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
//use Symfony\Component\HttpClient\HttpClient;
use Twilio\Rest\Client;

use Doctrine\DBAL\Connection;

class EmailController extends AbstractController
{
    private $connection;
    public function __construct(Connection $connection) {
        $this->connection = $connection;
    }
    
    
    /**
     * @Route("/email", name="app_email")
     */
    public function index(MailerInterface $mailer, LoggerInterface $logger): Response
    {

        try{
            $sql = "select * from connection";
        $statement = $this->connection->executeQuery($sql); 
        $result = $statement->fetchAllAssociative();

            $sqlSum = "select sum(Nbconnect) from connection";
        $statement = $this->connection->executeQuery($sqlSum); 
        $resultSum = $statement->fetchOne();

        // Your Twilio credentials
        $accountSid = 'AC37f82411bd9eee1c077bf0080fde855a'; //to modify
        $authToken = '1f00ad577c942262024d97956ac218f3';// to modify
        $twilioPhoneNumber = '+12314030665';// to modify

         // Recipient phone number
         $toPhoneNumber = '+14389279231'; //to modify

         // Create a Twilio client
        $twilio = new Client($accountSid, $authToken);
        

        if ($resultSum > 200) {          
            

            // Envoi d'email
            //The sender mail is store inside mailer.yaml
            $email = (new TemplatedEmail())
                ->to('abdel.eddaoui@alstefgroup.com')//to modify
                ->subject('Oracle connection exceeding')
                ->cc('vanick.djamen-djofang@alstefgroup.com')
                ->htmlTemplate('email/welcome.html.twig')// to modify
                ->context([
                    'listConnect' => $result,
                    'sumConnect' => $resultSum
                ]);
                
                        

            $mailer->send($email);
                 // Envoi SMS
           $twilio->messages->create(
                $toPhoneNumber,
                [
                    'from' => $twilioPhoneNumber,
                    'body' => 'Attention nombre de connections vers Oracle dépassé regadez vos mails!!!                             the number of connection to Oracle exceed look at your mail!!!'
                ]
            );
            // Rendu de la page avec les données
        return $this->render('email/welcome.html.twig', [
            'listConnect' => $result,
            'sumConnect' => $resultSum,
            'email' => $email 
        ]);                 
                              
              
        }            
        $logger->debug("001A>>Controller: EmailController. Method: index. Route : Email : Empty-> " . var_export(json_encode($result), true));
        
        }
        catch (\Throwable $e) {
            $errorMessage = $e->getMessage();
            
            // Afficher l'erreur ou la journaliser
            return $this->render('email/index.html.twig', [
                'controller_name' => 'EmailController', 'message' =>  $errorMessage
            ]);
        }
        

            
        
    }

    
}
