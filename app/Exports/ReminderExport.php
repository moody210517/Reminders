<?php

namespace App\Exports;

use App\Models\Reminder;
use Maatwebsite\Excel\Concerns\FromCollection;

class ReminderExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $isTrigger = "from";
    protected $from_date = '';
    protected $to_date = '';
    protected $start_date = '';
    protected $expiry_date = '';
    protected $customer_id = '';
    protected $service_id = '';

        
    function __construct($isTrigger, $from_date, $to_date, $start_date , $expiry_date, $customer_id, $service_id) {
        $this->isTrigger = $isTrigger;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
        $this->start_date = $start_date;
        $this->expiry_date = $expiry_date;
        $this->customer_id = $customer_id;
        $this->service_id = $service_id;
    }

    public function collection()
    {   


        $status = 1;
        $search = "";
        
        $isTrigger = $this->isTrigger;
        $from_date = $this->from_date;
        $to_date = $this->to_date;
        $start_date = $this->start_date;
        $expiry_date = $this->expiry_date;
        $customer_id = $this->customer_id;
        $service_id = $this->service_id;


        $reminders = Reminder::join('customers', 'reminder.customer_id', '=', 'customers.id')
            ->join('services', 'reminder.service_id', '=', 'services.id')
            ->join('duration', 'reminder.duration', '=', 'duration.id')
            ->join('remind_me_when', 'reminder.reminder_me_when', '=', 'remind_me_when.id')
            ->where('status', $status)
            ->where(function($query) use($search){
                $query->where('reminder.detail', 'like', '%'.$search.'%')
                ->orWhere('customers.company_name', 'like', '%'.$search.'%')
                ->orWhere('services.name', 'like', '%'.$search.'%')
                ->orWhere('duration.name', 'like', '%'.$search.'%')
                ->orWhere('reminder.expiry', 'like', '%'.$search.'%')
                ->orWhere('remind_me_when.name', 'like', '%'.$search.'%');

            })
            ->select('reminder.id' , 'customers.company_name as customer' , 'services.name as service' , 'reminder.created_at' , 'reminder.duration' 
            , 'reminder.detail', 'reminder.start_date');

        
        if($isTrigger == "from"){
            if($from_date != "" && $to_date != null){
                $reminders = $reminders->whereBetween('reminder.created_at', [$from_date." 00:00:00", $to_date." 23:59:59" ]);
            }
        }else if($isTrigger == "start_date"){
            if($start_date != ""){
                $reminders = $reminders->where('reminder.start_date', $start_date );
            }
        }else if($isTrigger == "expiry"){
            if($expiry_date != ""){
                $reminders = $reminders->where('reminder.expiry', $expiry_date );
            }
        }        

        if($service_id != 0){
            $reminders = $reminders->where('reminder.service_id', $service_id);
        }
        if($customer_id != 0){
            $reminders = $reminders->where('reminder.customer_id', $customer_id);
        }

        $reminders = $reminders->get();//skip($start)->take($length)->get();
        $reminders->map(function ($data) {
                    
            //$data->customer = Customer::find($data->customer_id)->company_name;
            //$data->service = Service::find($data->service_id)->name;

            $data->created =  date('Y-m-d', strtotime($data->created_at));
            if($data->duration == 1){
                $data->duration = "Monthly";
            }else{
                $data->duration = "Annual";
            }            
            return $data;
        
        });

        return $reminders;
        //return Reminder::where('start_date', $this->start_date)->get();
    }

    public function headings(): array
    {
        return [
            'Reminder #',
            'Date Created',
            'Customer',
            'Service',
            'Detail',
            'Start Date',
            'Expiry Date',
            'Duration',
        ];
    }

    public function map($transaction): array
    {
        return [
            $transaction->id,
            $transaction->created,
            $transaction->customer,
            $transaction->service,
            $transaction->detail,
            $transaction->start_date,
            $transaction->expiry,
            $transaction->duration,
        ];
    }

}
