<?php
namespace RedCrossQuest;

class QueteurMapper extends Mapper
{
    public function getQueteurs($query)
    {

      $sql = "SELECT q.`id`,
    q.`email`,
    q.`first_name`,
    q.`last_name`,
    q.`minor`,
    q.`secteur`,
    q.`nivol`,
    q.`mobile`,
    q.`created`,
    q.`updated`,
    q.`ul_id`,
    q.`notes`
FROM `queteur` q
";
      if($query!==null)
      {
        $sql .= "
WHERE UPPER(q.`first_name`) like concat('%', UPPER(:first_name), '%')
OR    UPPER(q.`last_name`)  like concat('%', UPPER(:last_name), '%')
OR    UPPER(q.`nivol`)      like concat('%', UPPER(:nivol), '%')
";
      }

      $this->logger->error($sql) ;

      $stmt = $this->db->prepare($sql);
      if($query!==null)
      {
        $result = $stmt->execute([
          "first_name" => $query,
          "last_name"  => $query,
          "nivol"      => $query
        ]);

      }
      else
      {
        $result = $stmt->execute([]);
      }

      $results = [];
      $i=0;
      while($row = $stmt->fetch())
      {
        $results[$i++] =  new QueteurEntity($row);
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
    q.`minor`,
    q.`secteur`,
    q.`nivol`,
    q.`mobile`,
    q.`created`,
    q.`updated`,
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

    public function update(QueteurEntity $queteur) {
        $sql =
          "UPDATE `queteur`
SET
`first_name`  = :first_name,
`last_name`   = :last_name,
`email`       = :email ,
`secteur`     = :secteur,
`nivol`       = :nivol,
`mobile`      = :mobile,
`updated`     = NOW(),
`notes`       = :notes,
`ul_id`       = :ul_id,
`minor`       = :minor
WHERE `id` = :id;
";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
          "first_name" => $queteur->first_name,
          "last_name"  => $queteur->last_name,
          "email"      => $queteur->email,
          "secteur"    => $queteur->secteur,
          "nivol"      => $queteur->nivol,
          "mobile"     => $queteur->mobile,
          "notes"      => $queteur->notes,
          "ul_id"      => $queteur->ul_id,
          "minor"      => $queteur->minor,
          "id"         => $queteur->id
        ]);

      $this->logger->warning($stmt->rowCount());


        if(!$result) {
            throw new Exception("could not save record");
        }
    }
}
