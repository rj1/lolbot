<?php
namespace lolbot\entities;

use knivey\tools;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table("Networks")]
class Network
{
    //Cant be readonly due to doctrine bug on remove
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(updatable: false)]
    public int $id;

    #[ORM\Column]
    public string $name;

    #[ORM\Column(updatable: false)]
    public \DateTimeImmutable $created;

    /**
     * @var Collection<int, Bot>
     */
    #[ORM\OneToMany(targetEntity: Bot::class, mappedBy: "network")]
    protected Collection $bots;

    /**
     * @var Collection<int, Ignore>
     */
    #[ORM\ManyToMany(targetEntity: Ignore::class, mappedBy: 'networks')]
    #[ORM\JoinTable(name: "Ignore_Network")]
    protected Collection $ignores;

    /**
     * @var Collection<int, Server>
     */
    #[ORM\OneToMany(targetEntity: Server::class, mappedBy: "network")]
    protected Collection $servers;

    public function __construct()
    {
        $this->created = new \DateTimeImmutable();
        $this->bots = new ArrayCollection();
        $this->ignores = new ArrayCollection();
    }

    public function addServer(Server $server) {
        $this->servers[] = $server;
    }

    public function addBot(Bot $bot) {
        $this->bots[] = $bot;
    }

    public function getBots() {
        return $this->bots;
    }

    public function getIgnores() {
        return $this->ignores;
    }

    public function addIgnore(Ignore $ignore) {
        $this->ignores[] = $ignore;
    }

    public function __toString():string {
        return "id: {$this->id} name: {$this->name} created: ".$this->created->format('r');
    }
}
