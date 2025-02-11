<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CampaignVoteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $url;
    public $contactName;
    public $companyName;

    /**
     * Create a new message instance.
     *
     * @param string $url
     * @param string $contactName
     * @param string $companyName
     * @return void
     */
    public function __construct($url, $contactName, $companyName)
    {
        $this->url = $url;
        $this->contactName = $contactName;
        $this->companyName = $companyName;
    }

    /**
     * Construire le message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('campaignVoteMail')
            ->with([
                'url' => $this->url,
                'contactName' => $this->contactName,
                'companyName' => $this->companyName,
            ])
            ->subject('Votez pour votre projet préféré');
    }
}
