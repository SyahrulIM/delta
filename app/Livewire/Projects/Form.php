<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Component;

class Form extends Component
{
    public $projectId;
    public $code;
    public $name;
    public $location;
    public $contact_name;
    public $contact_phone;

    public function mount($project = null)
    {
        if ($project) {
            $data = Project::findOrFail($project);
            $this->projectId     = $data->id;
            $this->code          = $data->code;
            $this->name          = $data->name;
            $this->location      = $data->location;
            $this->contact_name  = $data->contact_name;
            $this->contact_phone = $data->contact_phone;
        }
    }

    public function save()
    {
        $this->validate([
            'code'  => 'required',
            'name'  => 'required',
        ]);

        Project::updateOrCreate(
            ['id' => $this->projectId],
            [
                'code'          => $this->code,
                'name'          => $this->name,
                'location'      => $this->location,
                'contact_name'  => $this->contact_name,
                'contact_phone' => $this->contact_phone,
            ]
        );

        session()->flash('success', 'Project saved successfully.');
        return redirect()->route('projects.index');
    }

    public function render()
    {
        return view('livewire.projects.form');
    }
}
