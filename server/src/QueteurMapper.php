<?php
namespace RedCrossQuest;

class QueteurMapper extends Mapper
{
    public function getQueteurs() {
        $sql = "SELECT q.`id`,
    q.`email`,
    q.`first_name`,
    q.`last_name`,
    q.`secteur`,
    q.`nivol`,
    q.`mobile`,
    q.`created`,
    q.`updated`,
    q.`parent_authorization`,
    q.`temporary_volunteer_form`,
    q.`notes`,
    q.`ul_id`
FROM `queteur` q
";
        $stmt = $this->db->query($sql);

        $results = [];
      $i=0;
        while($row = $stmt->fetch()) {
           $results[$i++] = new QueteurEntity($row);
        }

      return $results;
    }

    /**
     * Get one ticket by its ID
     *
     * @param int $ticket_id The ID of the ticket
     * @return TicketEntity  The ticket
     */
    public function getQueteurById($queteur_id) {
        $sql = "SELECT q.`id`,
    q.`email`,
    q.`first_name`,
    q.`last_name`,
    q.`secteur`,
    q.`nivol`,
    q.`mobile`,
    q.`created`,
    q.`updated`,
    q.`parent_authorization`,
    q.`temporary_volunteer_form`,
    q.`notes`,
    q.`ul_id`
FROM `queteur` q
WHERE q.id = :queteur_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute(["queteur_id" => $queteur_id]);

        if($result) {
            return new QueteurEntity($stmt->fetch());
        }

    }
        /*
    public function save(TicketEntity $ticket) {
        $sql = "insert into tickets
            (title, description, component_id) values
            (:title, :description, 
            (select id from components where component = :component))";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            "title" => $ticket->getTitle(),
            "description" => $ticket->getDescription(),
            "component" => $ticket->getComponent(),
        ]);

        if(!$result) {
            throw new Exception("could not save record");
        }
    }     */
}
