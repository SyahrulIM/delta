<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Livewire\Component;

class Index extends Component
{
    public $projects;

    public function mount()
    {
        $this->projects = Project::orderBy('id','desc')->get();
    }

    public function delete($id)
    {
        Project::findOrFail($id)->delete();
        session()->flash('success', 'Project deleted successfully.');
        $this->projects = Project::orderBy('id','desc')->get();
    }

    public function render()
    {
        return view('livewire.projects.index');
    }
}
