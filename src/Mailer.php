<?php 

namespace Nebiros\PhpUtils;

/** 
 * Mailer.
 * 
 * @author nebiros
 */
class Mailer 
{ 
    protected $_mailsFilePath = null; 
  
    protected $_from = null; 
  
    protected $_mails = array(); 
  
    protected $_subject = null; 
  
    protected $_reply = null; 
  
    protected $_message = null; 
  
    protected $_messageText = null; 
  
    protected $_messageFile = null; 
  
    protected $_attachment = null; 
  
    protected $_isXhtml = false; 
  
    protected $_headers = null;

    protected $_isCli = false;

    protected $_errors = array();
  
    public function __construct(Array $options, $isCli = false) { 
        $this->_isCli = $isCli;

        $this->_from = $options["from"];   
        $this->_reply = $options["reply"];
        $this->_subject = $options["subject"];           
        if (true === empty($this->_subject)) { 
            throw new \InvalidArgumentException("Mail subject argument can't be empty"); 
        }

        $this->_isXhtml = (bool) $options["is_html"]; 
        $this->_attachment = $options["attach"]; 
  
        if (false === empty($this->_attachment) && false === is_file($this->_attachment)) { 
            throw new \InvalidArgumentException("Attachment must be real file"); 
        }

        if ((bool) $this->_isCli) {
            $this->_setupForCli($options);
        } else {
            $this->_setupForServer($options);
        }
  
        $this->_setMailData(); 
    }

    public function getErrors() {
        return $this->_errors;
    }

    protected function _setupForCli(Array $options) {
        $this->_mailsFilePath = $options["mails_file"];   
        if (true === empty($this->_mailsFilePath)) { 
            throw new \InvalidArgumentException("mails_file argument can't be empty"); 
        } 
          
        if (false === empty($this->_mailsFilePath) && false === is_file($this->_mailsFilePath)) { 
            throw new \InvalidArgumentException("Mails file should be a file! :O"); 
        } 
  
        $this->_messageText = $options["message_text"]; 
        $this->_messageFile = $options["message_file"]; 
          
        if (true === empty($this->_messageText) && true === empty($this->_mailsFilePath)) { 
            throw new \InvalidArgumentException("Mail message argument can't be empty, please use --message_text or --message_file argument"); 
        } 
          
        if (false === empty($this->_messageFile) && false === is_file($this->_messageFile)) { 
            throw new \InvalidArgumentException("Message file should be a file"); 
        }

        $this->_mails = $this->getEmailsFromFilePath();
    }

    protected function _setupForServer(Array $options) {
        $this->_mails = (array) $options["mails"];
        if (empty($this->_mails)) {
            throw new \InvalidArgumentException("Mails argument can't be empty"); 
        }

        $this->_messageText = $options["message_text"];
        if (empty($this->_messageText)) { 
            throw new \InvalidArgumentException("Mail message argument can't be empty"); 
        }
    }
  
    public function getEmailsFromFilePath() { 
        if (true === empty($this->_mailsFilePath)) { 
            throw new \InvalidArgumentException("mails file can't be empty"); 
        } 
  
        try { 
            // Open file. 
            // $rscMailsFile = fopen($this->strMailsFile, "r"); 
  
            $mails = file($this->_mailsFilePath); 
        } catch (\Exception $e) { 
            throw new \InvalidArgumentException("can't get mails from this file {$this->_mailsFilePath} - ({$e->getMessage()})"); 
        } 
  
        return $mails; 
    } 
  
    protected function _setMailData() { 
        if (false === empty($this->_messageFile)) { 
            $this->_message = file_get_contents($this->_messageFile); 
        } else if (false === empty($this->_messageText)) { 
            $this->_message = $this->_messageText; 
        } 
          
        if (false === empty($this->_messageFile) && false === empty($this->_messageText)) { 
            $this->_message = $this->_messageText;             
            $this->_message .= file_get_contents($this->_messageFile); 
        } 
          
        $this->_headers = $this->_setMailHeaders(); 
    } 
  
    protected function _setMailHeaders() { 
        $mailHeaders = ""; 
        $boundaryHash = md5(date("r", time())); 
          
        if (true === empty($this->_from)) { 
            $this->_from = DEFAULT_EMAIL_FROM; 
        } 
  
        ini_set("sendmail_from", $this->_from); 
        $mailHeaders .= "From: {$this->_from}\r\n"; 
          
        if (false === empty($this->_reply)) { 
            $mailHeaders .= "Reply-To: {$this->_reply}\r\n"; 
        } 
  
        $mailHeaders .= "MIME-Version: 1.0\r\n"; 
  
        if (false === empty($this->_attachment)) { 
            $mailHeaders .= "Content-Type: multipart/mixed; boundary=\"mixed-{$boundaryHash}\""; 
        } else { 
            $mailHeaders .= "Content-Type: multipart/alternative; boundary=\"alt-{$boundaryHash}\""; 
        }

        $mailHeaders .= "X-Sender-IP: {$_SERVER[SERVER_ADDR]}\r\n";
        $mailHeaders .= "Date: " . date("n/d/Y g:i A") . "\r\n";
  
        $this->_setMessageWithHeaders($boundaryHash); 
        return $mailHeaders; 
    } 
  
    /** 
     * Set message with all the headers needed. 
     * 
     * @param string $boundaryHash Boundary hash. 
     * @return void 
     */
    protected function _setMessageWithHeaders($boundaryHash) { 
        ob_start(); 
  
        // TODO: must be a heredoc. 
        // set some headers if we have a file to be attached. 
        if (false === empty($this->_attachment)) { 
?> 
--mixed-<?php echo $boundaryHash . "\n" ?> 
Content-Type: multipart/alternative; boundary="alt-<?php echo $boundaryHash ?>"<?php echo "\n" ?> 
<?php 
        } 
?> 
--alt-<?php echo $boundaryHash . "\n" ?> 
Content-Type: text/plain; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit
<?php echo $this->_message . "\n" ?> 
--alt-<?php echo $boundaryHash . "\n" ?> 
Content-Type: text/html; charset="iso-8859-1"
Content-Transfer-Encoding: 7bit 
<?php echo $this->_message . "\n" ?> 
--alt-<?php echo $boundaryHash ?>--<?php echo "\n" ?> 
<?php 
        // add attachment. 
        if (false === empty($this->_attachment)) { 
?> 
--mixed-<?php echo $boundaryHash . "\n" ?> 
Content-Type: "<?php echo mime_content_type($this->_attachment) ?>"; name="<?php echo basename($this->_attachment) ?>"
Content-Transfer-Encoding: base64 
Content-Disposition: attachment; file="<?php echo basename($this->_attachment) ?>"
<?php 
            // encode attachment and add it to the mail message. 
            $attachmentEncoded = chunk_split(base64_encode(file_get_contents($this->_attachment))); 
?> 
<?php echo $attachmentEncoded . "\n" ?> 
--mixed-<?php echo $boundaryHash ?>-- 
<?php 
        } 
          
        // Now set the mail message with all headers needed :). 
        $this->_message = ob_get_clean(); 
    } 
  
    /** 
     * Send mails. 
     * 
     * @return void 
     */
    public function sendMails() { 
        if ($this->_isCli) {
            global $errors; 
        }
        
        $this->_errors = array();
  
        // Loop every line. 
        foreach ($this->_mails AS $index => $mail) { 
            if (false === mail($mail, $this->_subject, $this->_message, $this->_headers)) { 
                $msg = "Can't be sent mail for {$mail}"; 

                if ($this->_isCli) {
                    // NOTE: I'll do print a message when the mail can't be sent because I'm working in the CLI. 
                    echo "==> can't send email to this recipient: {$mail}"; 
                    $errors[] = $msg;
                } else {
                    $this->_errors[] = $msg;
                }
            } else { 
                if ($this->_isCli) {
                    echo "==> mail sent to {$mail}";
                }
            } 
        } 
    } 
}
