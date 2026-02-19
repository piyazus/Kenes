/**
 * Search and Sorting Algorithms Library
 */

// Search Algorithms

/**
 * Linear Search
 * @param {Array} arr - The array to search
 * @param {any} target - The value to search for
 * @return {number} - The index of the target, or -1 if not found
 */
function linearSearch(arr, target) {
    for (let i = 0; i < arr.length; i++) {
        if (arr[i] === target) {
            return i;
        }
    }
    return -1;
}

/**
 * Binary Search
 * Note: The array must be sorted for binary search to work correctly.
 * @param {Array} arr - The sorted array to search
 * @param {any} target - The value to search for
 * @return {number} - The index of the target, or -1 if not found
 */
function binarySearch(arr, target) {
    let left = 0;
    let right = arr.length - 1;

    while (left <= right) {
        const mid = Math.floor((left + right) / 2);

        if (arr[mid] === target) {
            return mid;
        }

        if (arr[mid] < target) {
            left = mid + 1;
        } else {
            right = mid - 1;
        }
    }

    return -1;
}


// Sorting Algorithms

/**
 * Bubble Sort
 * @param {Array} arr - The array to sort
 * @return {Array} - The sorted array
 */
function bubbleSort(arr) {
    const n = arr.length;
    let swapped;
    do {
        swapped = false;
        for (let i = 0; i < n - 1; i++) {
            if (arr[i] > arr[i + 1]) {
                // Swap elements
                let temp = arr[i];
                arr[i] = arr[i + 1];
                arr[i + 1] = temp;
                swapped = true;
            }
        }
    } while (swapped);
    return arr;
}

/**
 * Insertion Sort
 * @param {Array} arr - The array to sort
 * @return {Array} - The sorted array
 */
function insertionSort(arr) {
    for (let i = 1; i < arr.length; i++) {
        let key = arr[i];
        let j = i - 1;

        /* Move elements of arr[0..i-1], that are greater than key,
           to one position ahead of their current position */
        while (j >= 0 && arr[j] > key) {
            arr[j + 1] = arr[j];
            j = j - 1;
        }
        arr[j + 1] = key;
    }
    return arr;
}

/**
 * Quick Sort
 * @param {Array} arr - The array to sort
 * @return {Array} - The sorted array
 */
function quickSort(arr) {
    if (arr.length <= 1) {
        return arr;
    }

    const pivot = arr[arr.length - 1];
    const left = [];
    const right = [];

    for (let i = 0; i < arr.length - 1; i++) {
        if (arr[i] < pivot) {
            left.push(arr[i]);
        } else {
            right.push(arr[i]);
        }
    }

    return [...quickSort(left), pivot, ...quickSort(right)];
}

/**
 * Merge Sort
 * @param {Array} arr - The array to sort
 * @return {Array} - The sorted array
 */
function mergeSort(arr) {
    if (arr.length <= 1) {
        return arr;
    }

    const mid = Math.floor(arr.length / 2);
    const left = arr.slice(0, mid);
    const right = arr.slice(mid);

    return merge(mergeSort(left), mergeSort(right));
}

/**
 * Helper function for Merge Sort
 * @param {Array} left - The left sorted array
 * @param {Array} right - The right sorted array
 * @return {Array} - The merged sorted array
 */
function merge(left, right) {
    let result = [];
    let leftIndex = 0;
    let rightIndex = 0;

    while (leftIndex < left.length && rightIndex < right.length) {
        if (left[leftIndex] < right[rightIndex]) {
            result.push(left[leftIndex]);
            leftIndex++;
        } else {
            result.push(right[rightIndex]);
            rightIndex++;
        }
    }

    return result.concat(left.slice(leftIndex)).concat(right.slice(rightIndex));
}

// Export functions if using modules, otherwise they are global in browser if included via script tag
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = {
        linearSearch,
        binarySearch,
        bubbleSort,
        insertionSort,
        quickSort,
        mergeSort
    };
}
