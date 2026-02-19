<?php include 'includes/header.php'; ?>

<main class="container" style="padding-top: 40px; padding-bottom: 80px;">
    <h1>Algorithms Playground</h1>
    <p>Test the search and sorting algorithms below.</p>

    <section style="margin-top: 40px;">
        <h2>Array Input</h2>
        <p>Enter comma-separated numbers (e.g., 64, 34, 25, 12):</p>
        <input type="text" id="arrayInput" value="64, 34, 25, 12, 22, 11, 90"
            style="padding: 10px; width: 100%; max-width: 400px; margin-bottom: 20px;">
        <p>Current Array: <span id="currentArrayDisplay" style="font-family: monospace; font-weight: bold;"></span></p>
    </section>

    <div
        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; margin-top: 40px;">

        <!-- Sorting Section -->
        <div class="glass-card">
            <h3>Sorting Algorithms</h3>
            <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px;">
                <button onclick="runBubbleSort()" class="btn btn-primary"
                    style="color: white; padding: 10px 20px;">Bubble Sort</button>
                <button onclick="runInsertionSort()" class="btn btn-primary"
                    style="color: white; padding: 10px 20px;">Insertion Sort</button>
                <button onclick="runQuickSort()" class="btn btn-primary" style="color: white; padding: 10px 20px;">Quick
                    Sort</button>
                <button onclick="runMergeSort()" class="btn btn-primary" style="color: white; padding: 10px 20px;">Merge
                    Sort</button>
            </div>
            <div style="margin-top: 20px;">
                <strong>Result:</strong>
                <pre id="sortResult"
                    style="background: #f4f4f4; padding: 15px; border-radius: 5px; margin-top: 10px;"></pre>
            </div>
        </div>

        <!-- Searching Section -->
        <div class="glass-card">
            <h3>Search Algorithms</h3>
            <div style="margin-top: 20px; margin-bottom: 20px;">
                <label>Target Value:</label>
                <input type="number" id="targetInput" value="22" style="padding: 8px; width: 100px;">
            </div>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button onclick="runLinearSearch()" class="btn btn-primary"
                    style="color: white; padding: 10px 20px;">Linear Search</button>
                <button onclick="runBinarySearch()" class="btn btn-primary"
                    style="color: white; padding: 10px 20px;">Binary Search</button>
            </div>
            <div style="margin-top: 20px;">
                <strong>Result:</strong>
                <pre id="searchResult"
                    style="background: #f4f4f4; padding: 15px; border-radius: 5px; margin-top: 10px;"></pre>
            </div>
        </div>

    </div>
</main>

<script src="js/algorithms.js"></script>
<script>
    function getArray() {
        const input = document.getElementById('arrayInput').value;
        return input.split(',').map(num => parseFloat(num.trim())).filter(num => !isNaN(num));
    }

    function updateArrayDisplay() {
        const arr = getArray();
        document.getElementById('currentArrayDisplay').textContent = JSON.stringify(arr);
    }

    document.getElementById('arrayInput').addEventListener('input', updateArrayDisplay);
    // Initial display
    updateArrayDisplay();

    function displayResult(elementId, text) {
        document.getElementById(elementId).textContent = text;
    }

    function runBubbleSort() {
        const arr = getArray();
        const start = performance.now();
        const sorted = bubbleSort([...arr]);
        const end = performance.now();
        displayResult('sortResult', `Sorted: ${JSON.stringify(sorted)}\nTime: ${(end - start).toFixed(4)}ms`);
    }

    function runInsertionSort() {
        const arr = getArray();
        const start = performance.now();
        const sorted = insertionSort([...arr]);
        const end = performance.now();
        displayResult('sortResult', `Sorted: ${JSON.stringify(sorted)}\nTime: ${(end - start).toFixed(4)}ms`);
    }

    function runQuickSort() {
        const arr = getArray();
        const start = performance.now();
        const sorted = quickSort([...arr]);
        const end = performance.now();
        displayResult('sortResult', `Sorted: ${JSON.stringify(sorted)}\nTime: ${(end - start).toFixed(4)}ms`);
    }

    function runMergeSort() {
        const arr = getArray();
        const start = performance.now();
        const sorted = mergeSort([...arr]);
        const end = performance.now();
        displayResult('sortResult', `Sorted: ${JSON.stringify(sorted)}\nTime: ${(end - start).toFixed(4)}ms`);
    }

    function runLinearSearch() {
        const arr = getArray();
        const target = parseFloat(document.getElementById('targetInput').value);
        const start = performance.now();
        const index = linearSearch(arr, target);
        const end = performance.now();
        displayResult('searchResult', `Index: ${index}\nTime: ${(end - start).toFixed(4)}ms`);
    }

    function runBinarySearch() {
        // Binary search requires sorted array
        const arr = getArray();
        // Sort first because binary search expects sorted input
        // Ideally we would tell the user to sort first, but for UX we can sort a copy
        const sortedArr = [...arr].sort((a, b) => a - b);

        const target = parseFloat(document.getElementById('targetInput').value);
        const start = performance.now();
        const index = binarySearch(sortedArr, target);
        const end = performance.now();

        displayResult('searchResult', `(Searched in sorted array: ${JSON.stringify(sortedArr)})\nIndex: ${index}\nTime: ${(end - start).toFixed(4)}ms`);
    }
</script>

<?php include 'includes/footer.php'; ?>