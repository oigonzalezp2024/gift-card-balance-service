<?php 
namespace App\Infrastructure\Tool;

use App\Domain\Ports\Output\TransactionManagerInterface;
use PDO;
use PDOException;

class PDOTransactionManager implements TransactionManagerInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function executeInTransaction(callable $operation)
    {
        $this->pdo->beginTransaction();
        try {
            $result = $operation();
            $this->pdo->commit();
            return $result;
        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw new \Exception("Ocurrió un error interno del sistema (DB: " . hash('crc32', $e->getMessage()) . "). Intente de nuevo o contacte a soporte.", 0);
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }
}
