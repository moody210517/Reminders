<?php

namespace App\Mail;


use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Swift_Mailer;
use Swift_SmtpTransport;
use CustomGlobal\Website;
use CustomGlobal\Territory;

class CustomMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $layout_view_to_serve;
    public $host_folder;

    /**
     * Override Mailable functionality to support per-user mail settings
     *
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @return void
     */
    public function send(Mailer $mailer)
    {
        app()->call([$this, 'build']);

        //$config = config($this->host_folder .'.mail');
        // Set SMTP details for this host

        $configs = DB::table('settings')->get();        
        foreach($configs as $item){
            switch($item->name){
                case "SMPT Servcer":                    
                    $host = $item->value; 
                    break;
                case "SMPT Port" :
                    $port = $item->value; 

                    break;
                case "SMPT Username" :
                    $username = $item->value; 

                    break;
                case "SMPT Password" :
                    $password = $item->value; 

                    break;
                case "Encryption":
                    $encryption = $item->value; 

                    break;
            }
        }

        //$host =  $config['host'];
        //$port = $config['port'];
        //$encryption  = $config['encryption'];

        $transport = new Swift_SmtpTransport( $host, $port, $encryption );
        $transport->setUsername( $username );
        $transport->setPassword( $password );
        $mailer->setSwiftMailer(new Swift_Mailer($transport));

        $mailer->send($this->buildView(), $this->buildViewData(), function ($message) use($config) {
            $message->from([ 'refund@platinumhomecareuk.co.uk' => 'name' ]);
            $this->buildFrom($message)
                 ->buildRecipients($message)
                 ->buildSubject($message)
                 ->buildAttachments($message)
                 ->runCallbacks($message);
        });
    }

    /**
     * Calculate the template we need to serve.
     * $entity can be any object but it must contain a 
     * $website_id and $territory_id, as that is used
     * to calculate the path.
     */
    public function get_custom_mail_view($view_filename, $entity)
    {
        if(empty($view_filename)) {
            throw new Exception('The get_custom_mail_view method requires a view to be passed as parameter 1.');
        }

        if(empty($entity->website_id) || empty($entity->territory_id)) {
            throw new Exception('The get_custom_mail_view method must be passed an object containing a website_id and territory_id value.');
        }

        // Get the website and territory
        $website = Website::findOrFail($entity->website_id);
        $territory = Territory::findOrFail($entity->territory_id);

        $view_to_serve = false;
        $layout_view_to_serve = false;

        // Be sure to replace . with _, as Laravel doesn't play nice with dots in folder names
        $host_folder = str_replace('.', '_', $website->website_domain);
        $this->host_folder = $host_folder; // Used for mail config later

        /***
            Truncated for readability.  What's in this area isn't really important to this answer.
        ***/

        $this->layout_view_to_serve = $layout_view_to_serve;

        return $view_to_serve;
    }
}