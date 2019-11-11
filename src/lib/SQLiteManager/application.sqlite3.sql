BEGIN TRANSACTION;

CREATE TABLE IF NOT EXISTS "text_atom_translation" (
	"fk_text_atom"	INTEGER,
	"translation"	VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS "text_atom_color" (
	"fk_text_atom"	INTEGER,
	"color"	VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS "text_atoms" (
	"id"	INTEGER PRIMARY KEY AUTOINCREMENT,
	"chars"	VARCHAR(255),
	"is_word"	INTEGER,
	"fk_text"	INTEGER,
	"created"	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	"deleted"	INTEGER DEFAULT 0,
	"order"	INTEGER
);

CREATE TABLE IF NOT EXISTS "languages" (
	"id"	INTEGER PRIMARY KEY AUTOINCREMENT,
	"title"	VARCHAR(255),
	"character_range"	TEXT,
	"created"	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	"deleted"	INTEGER DEFAULT 0
);

CREATE TABLE IF NOT EXISTS "texts" (
	"id"	INTEGER PRIMARY KEY AUTOINCREMENT,
	"title"	VARCHAR(255),
	"text"	TEXT,
	"audio"	VARCHAR(255),
	"created"	TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
	"deleted"	INTEGER DEFAULT 0,
	"fk_language"	INTEGER
);

INSERT INTO "languages" VALUES (1,'Koine Griechisch','\x{0370}-\x{03ff}\x{1f00}-\x{1fff}','2019-10-31 14:47:58',0);
INSERT INTO "languages" VALUES (2,'Deutsch','\x{0041}-\x{005A}\x{0061}-\x{007A}\x{00C0}-\x{00D6}\x{00D9}-\x{00DD}\x{00DF}-\x{00F6}\x{00F9}-\x{00FD}\x{00FF}','2019-11-01 09:10:27',0);

COMMIT;
