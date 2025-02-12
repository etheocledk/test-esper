<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InvitationEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $company_name;
    public $email;
    public $password;
    /**
     * Créer une nouvelle instance de message.
     *
     * @param string $company_name
     * @param string $email
     * @param string $password
     */
    public function __construct($company_name, $email, $password)
    {
        $this->company_name = $company_name;
        $this->email = $email;
        $this->password = $password;
    }

    /**
     * Construire le message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('companyMemberMail')
            ->with([
                'company_name' => $this->company_name,
                'email' => $this->email,
                'password' => $this->password,
            ])
            ->subject('Invitation à rejoindre Esper');
    }
}
