<?php

// Читаем ввод
$input = trim(stream_get_contents(STDIN));
$lines = explode("\n", $input);

// Читаем размер лабиринта
list($rows, $cols) = array_map('intval', explode(" ", trim($lines[0])));

// Читаем матрицу лабиринта
$maze = [];
for ($i = 0; $i < $rows; $i++) {
    $maze[] = array_map('intval', explode(" ", trim($lines[$i + 1])));
}

// Читаем стартовые и конечные координаты
$coords = array_map('intval', explode(" ", trim($lines[$rows + 1])));
if (count($coords) !== 4) {
    fwrite(STDERR, "Координаты должны содержать четыре числа (строка_старта столбец_старта строка_конца столбец_конца)\n");
    exit(1);
}
list($startRow, $startCol, $endRow, $endCol) = $coords;

// Проверяем валидность координат
if (
    $startRow < 0 || $startRow >= $rows || $startCol < 0 || $startCol >= $cols ||
    $endRow < 0 || $endRow >= $rows || $endCol < 0 || $endCol >= $cols
) {
    fwrite(STDERR, "Координаты выходят за границы лабиринта\n");
    exit(1);
}

// Движения: вверх, вниз, влево, вправо
$directions = [
    [-1, 0], [1, 0], [0, -1], [0, 1]
];

// Приоритетная очередь для обработки узлов
$queue = new SplPriorityQueue();
$queue->insert([$startRow, $startCol], 0);

// Расстояния и пути
$distances = array_fill(0, $rows, array_fill(0, $cols, PHP_INT_MAX));
$distances[$startRow][$startCol] = 0;

$previous = array_fill(0, $rows, array_fill(0, $cols, null));

while (!$queue->isEmpty()) {
    list($currentRow, $currentCol) = $queue->extract();

    // Если достигли конца, прекращаем
    if ($currentRow === $endRow && $currentCol === $endCol) {
        break;
    }

    // Проверяем соседей
    foreach ($directions as [$dRow, $dCol]) {
        $newRow = $currentRow + $dRow;
        $newCol = $currentCol + $dCol;

        if ($newRow >= 0 && $newRow < $rows && $newCol >= 0 && $newCol < $cols && $maze[$newRow][$newCol] > 0) {
            $newDistance = $distances[$currentRow][$currentCol] + $maze[$newRow][$newCol];

            if ($newDistance < $distances[$newRow][$newCol]) {
                $distances[$newRow][$newCol] = $newDistance;
                $previous[$newRow][$newCol] = [$currentRow, $currentCol];
                $queue->insert([$newRow, $newCol], -$newDistance);
            }
        }
    }
}

// Восстанавливаем путь
$path = [];
$current = [$endRow, $endCol];

if ($distances[$endRow][$endCol] === PHP_INT_MAX) {
    fwrite(STDERR, "Нет пути из стартовой точки в конечную\n");
    exit(1);
}

while ($current !== null) {
    array_unshift($path, $current);
    $current = $previous[$current[0]][$current[1]];
}

// Выводим путь
foreach ($path as [$row, $col]) {
    echo "$row $col\n";
}

echo ".\n";
