<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250106003836 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment CHANGE commentaire commentaire VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE comment RENAME INDEX idx_comments_movie_id TO IDX_9474526C8F93B6FC');
        $this->addSql('ALTER TABLE comment RENAME INDEX idx_comments_user_id TO IDX_9474526CA76ED395');
        $this->addSql('ALTER TABLE likes CHANGE id id INT AUTO_INCREMENT NOT NULL, CHANGE movie_id movie_id INT DEFAULT NULL, CHANGE user_id user_id INT DEFAULT NULL, CHANGE comment_id comment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7D8F93B6FC FOREIGN KEY (movie_id) REFERENCES movies (id)');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7DF8697D13 FOREIGN KEY (comment_id) REFERENCES comment (id)');
        $this->addSql('ALTER TABLE likes ADD CONSTRAINT FK_49CA4E7DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX unique_user_movie_like ON likes (user_id, movie_id)');
        $this->addSql('CREATE UNIQUE INDEX unique_user_comment_like ON likes (user_id, comment_id)');
        $this->addSql('ALTER TABLE likes RENAME INDEX idx_like_movie_id TO IDX_49CA4E7D8F93B6FC');
        $this->addSql('ALTER TABLE likes RENAME INDEX idx_likes_comment_id TO IDX_49CA4E7DF8697D13');
        $this->addSql('ALTER TABLE likes RENAME INDEX idx_like_user_id TO IDX_49CA4E7DA76ED395');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment CHANGE commentaire commentaire VARCHAR(255) DEFAULT \'NULL\'');
        $this->addSql('ALTER TABLE comment RENAME INDEX idx_9474526ca76ed395 TO idx_comments_user_id');
        $this->addSql('ALTER TABLE comment RENAME INDEX idx_9474526c8f93b6fc TO idx_comments_movie_id');
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7D8F93B6FC');
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7DF8697D13');
        $this->addSql('ALTER TABLE likes DROP FOREIGN KEY FK_49CA4E7DA76ED395');
        $this->addSql('DROP INDEX unique_user_movie_like ON likes');
        $this->addSql('DROP INDEX unique_user_comment_like ON likes');
        $this->addSql('ALTER TABLE likes CHANGE id id INT NOT NULL, CHANGE movie_id movie_id INT NOT NULL, CHANGE comment_id comment_id INT NOT NULL, CHANGE user_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE likes RENAME INDEX idx_49ca4e7df8697d13 TO idx_likes_comment_id');
        $this->addSql('ALTER TABLE likes RENAME INDEX idx_49ca4e7d8f93b6fc TO idx_like_movie_id');
        $this->addSql('ALTER TABLE likes RENAME INDEX idx_49ca4e7da76ed395 TO idx_like_user_id');
        $this->addSql('ALTER TABLE user CHANGE roles roles LONGTEXT NOT NULL COLLATE `utf8mb4_bin`');
    }
}
