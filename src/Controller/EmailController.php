<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Filesystem\Filesystem;
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
    public function index( MailerInterface $mailer, LoggerInterface $logger): Response
    {
        $emailList = [];
        $filePath = $this->getParameter('kernel.project_dir') . '/public/mail.txt';
        $filesystem = new Filesystem();
        if ($filesystem->exists($filePath)) {
            $fileContent = file_get_contents($filePath);
            $emailList = explode("\n", $fileContent);
        }

        try{
            $sql = "select * from connection";
        $statement = $this->connection->executeQuery($sql); 
        $result = $statement->fetchAllAssociative();

            $sqlSum = "select sum(Nbconnect) from connection";
        $statement = $this->connection->executeQuery($sqlSum); 
        $resultSum = $statement->fetchOne();

            $sqlAck = "SELECT ACK FROM connection  ";
        $statement = $this->connection->executeQuery($sqlAck);
        $ackValue = $statement->fetchAllAssociative();
        // Your Twilio credentials
        $accountSid = 'AC37f82411bd9eee1c077bf0080fde855a'; //to modify
        $authToken = '1f00ad577c942262024d97956ac218f3';// to modify
        $twilioPhoneNumber = '+12314030665';// to modify

         // Recipient phone number
         $toPhoneNumber = '+14389279231'; //to modify

         // Create a Twilio client
        $twilio = new Client($accountSid, $authToken);
            
        // Envoi d'email
            //The sender mail is store inside mailer.yaml
            $email = (new TemplatedEmail())
                ->from('reporting@alstefgroup.com')
                ->to(...$emailList)//Add mail inside the text file
                ->subject('Oracle connection exceeding')
                ->cc('abdel.eddaoui@alstefgroup.com')
                ->htmlTemplate('email/welcome.html.twig')// to modify
                ->context([
                    'listConnect' => $result,
                    'sumConnect' => $resultSum
                ]);
        
            
        
            
        if ($resultSum > 200) {          
            
            foreach($ackValue as $value){
                if($value['ACK']  == NULL || $value['ACK']  == '' )
                    { 
                        $mailer->send($email);
                        $sqlInsert = "UPDATE connection SET ACK = 'OUI' WHERE ACK IS NULL OR ACK = '' ";
                        $statement = $this->connection->executeQuery($sqlInsert);
                        $value = $statement->fetchOne();                       
                        
                         // Envoi SMS
                        // $twilio->messages->create(
                        //     $toPhoneNumber,
                        //     [
                        //         'from' => $twilioPhoneNumber,
                        //         'body' => 'Attention nombre de connections vers Oracle dépassé regadez vos mails!!!                             the number of connection to Oracle exceed look at your mail!!!'
                        //     ]
                        // );
                        break;
                    } 
                else return die;
            }
                                  
            
                
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
