<?php
namespace Godamri\HyUtils\ResourceMapper;

class ResourceMapper
{
    protected $resources=[];
    protected $mappedResources=[];
    protected $resource = null;
    
    public function __invoke() : array
    {
        $this->mapResources();
        return $this->mappedResources;
    }
    
    private function mapResources() : void
    {
        foreach ($this->resources as $resource)
            $this->mapFields($resource);
    }
    private function mapFields($resource)
    {
        $this->resource = $resource;
        $tmpMappedResource = [];
        if(count((array)$resource)>0) {
            foreach ($this->map() as $key => $map)
                if (isset($resource->{$key}))
                    $tmpMappedResource[$key] = $map;
                else
                    $tmpMappedResource[$key] = null;
        }
        
        $this->mappedResources[] = $tmpMappedResource;
        unset($tmpMappedResource);
        unset($this->resources);
    }
}
