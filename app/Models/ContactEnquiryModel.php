<?php

namespace App\Models;

use CodeIgniter\Model;

class ContactEnquiryModel extends Model
{
    protected $table         = 'contact_enquiries';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $protectFields = true;
    protected $allowedFields = ['first_name', 'last_name', 'email', 'phone', 'subject', 'message', 'status', 'admin_note', 'ip_address'];
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
