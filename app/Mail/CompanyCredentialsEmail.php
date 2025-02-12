<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompanyCredentialsEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    public $password;
    /**
     * Créer une nouvelle instance de message.
     *
     * @param string $email
     * @param string $password
     */
    public function __construct($email, $password)
    {
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
        return $this->view('companyMail')
            ->with([
                'email' => $this->email,
                'password' => $this->password,
            ])
            ->subject('Bienvenue chez Esper');
    }
}
