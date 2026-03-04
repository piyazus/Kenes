/**
 * ============================================================
 * ALGORITHMS.JS — Sorting & Searching Algorithms Library
 * ============================================================
 * Purpose:  Implements fundamental sorting and searching algorithms
 *           manually (without built-in .sort() or Array helpers).
 *           These algorithms operate on JavaScript arrays that
 *           simulate data retrieved from a database.
 *
 * Algorithms Implemented:
 *   SORTING:
 *     - Bubble Sort    (Time: O(n²), Space: O(1), Stable)
 *     - Insertion Sort (Time: O(n²), Space: O(1), Stable)
 *     - Quick Sort     (Time: O(n log n) avg, Space: O(n), Unstable)
 *     - Merge Sort     (Time: O(n log n), Space: O(n), Stable)
 *
 *   SEARCHING:
 *     - Linear Search  (Time: O(n), Works on unsorted arrays)
 *     - Binary Search  (Time: O(log n), Requires sorted array)
 *
 * Academic Notes:
 *   - All algorithms are implemented from scratch
 *   - No built-in .sort(), .find(), .indexOf() used
 *   - Each function is fully commented step-by-step
 *   - Big-O time and space complexity annotated
 * ============================================================
 */


// =============================================
//  SEARCHING ALGORITHMS
// =============================================

/**
 * LINEAR SEARCH ALGORITHM
 * -----------------------------------------------
 * Searches for a target value by checking each element
 * in the array sequentially from left to right.
 *
 * How it works:
 *   1. Start at the first element (index 0)
 *   2. Compare the current element with the target
 *   3. If they match, return the current index
 *   4. If not, move to the next element
 *   5. If the end of the array is reached, return -1 (not found)
 *
 * Time Complexity:  O(n) — must check up to n elements
 * Space Complexity: O(1) — uses only one loop variable
 * Best Case:  O(1) — target is the first element
 * Worst Case: O(n) — target is the last element or not present
 *
 * Advantage:  Works on UNSORTED arrays (no pre-sorting needed)
 * Disadvantage: Slow for large datasets compared to Binary Search
 *
 * @param {Array} arr    - The array to search through
 * @param {any}   target - The value to look for
 * @return {number}      - Index of target, or -1 if not found
 */
function linearSearch(arr, target) {
    // Iterate through every element in the array, one at a time
    for (let i = 0; i < arr.length; i++) {
        // Compare the current element with the target value
        // Using strict equality (===) to match both value and type
        if (arr[i] === target) {
            // Target found — return its index position
            return i;
        }
    }
    // If the loop completes without finding the target,
    // return -1 to indicate the value is not in the array
    return -1;
}

/**
 * BINARY SEARCH ALGORITHM
 * -----------------------------------------------
 * Searches for a target value by repeatedly dividing
 * the search interval in half. This is much faster than
 * Linear Search but REQUIRES the array to be sorted first.
 *
 * How it works:
 *   1. Set two pointers: left = start, right = end of array
 *   2. Find the middle element: mid = floor((left + right) / 2)
 *   3. If arr[mid] === target, return mid (found!)
 *   4. If arr[mid] < target, search the RIGHT half (left = mid + 1)
 *   5. If arr[mid] > target, search the LEFT half (right = mid - 1)
 *   6. Repeat until left > right (value not found)
 *
 * PREREQUISITE: The array MUST be sorted in ascending order.
 *               If the array is not sorted, results are unreliable.
 *
 * Time Complexity:  O(log n) — halves the search space each step
 * Space Complexity: O(1) — iterative version uses constant space
 * Best Case:  O(1) — target is the middle element
 * Worst Case: O(log n) — target is at the extremes or absent
 *
 * @param {Array} arr    - The SORTED array to search through
 * @param {any}   target - The value to look for
 * @return {number}      - Index of target, or -1 if not found
 */
function binarySearch(arr, target) {
    // Initialize two pointers defining the search boundaries
    let left = 0;                  // Start of search range
    let right = arr.length - 1;    // End of search range

    // Continue searching while the range is valid
    // When left > right, the search space is exhausted
    while (left <= right) {
        // Calculate the middle index of the current search range
        // Math.floor() rounds down to get a valid integer index
        const mid = Math.floor((left + right) / 2);

        // Check if the middle element is the target
        if (arr[mid] === target) {
            // Target found at index mid — return it
            return mid;
        }

        // If the middle element is LESS than the target,
        // the target must be in the RIGHT half of the array
        if (arr[mid] < target) {
            left = mid + 1;   // Discard the left half (including mid)
        } else {
            // If the middle element is GREATER than the target,
            // the target must be in the LEFT half of the array
            right = mid - 1;  // Discard the right half (including mid)
        }
    }

    // Search range exhausted — target is not in the array
    return -1;
}


// =============================================
//  SORTING ALGORITHMS
// =============================================

/**
 * BUBBLE SORT ALGORITHM
 * -----------------------------------------------
 * Sorts an array by repeatedly stepping through the list,
 * comparing adjacent elements, and swapping them if they
 * are in the wrong order. The pass through the list is
 * repeated until no swaps are needed (array is sorted).
 *
 * How it works:
 *   1. Start at the beginning of the array
 *   2. Compare each pair of adjacent elements
 *   3. If arr[i] > arr[i+1], swap them
 *   4. After one full pass, the largest element "bubbles" to the end
 *   5. Repeat until a complete pass with no swaps occurs
 *
 * The name "Bubble Sort" comes from the way larger elements
 * "bubble up" to their correct position at the end of the array.
 *
 * Time Complexity:  O(n²) — two nested loops in worst case
 * Space Complexity: O(1) — sorts in-place, only uses temp variable
 * Best Case:  O(n) — array is already sorted (one pass, no swaps)
 * Worst Case: O(n²) — array is sorted in reverse order
 * Stability:  STABLE — equal elements maintain their relative order
 *
 * @param {Array} arr - The array to sort (modified in-place)
 * @return {Array}    - The sorted array (same reference)
 */
function bubbleSort(arr) {
    const n = arr.length;   // Store array length for efficiency
    let swapped;            // Flag to track if any swaps occurred in this pass

    // Outer loop: repeat passes until no swaps are needed
    do {
        swapped = false;    // Reset the swap flag for each new pass

        // Inner loop: compare each pair of adjacent elements
        for (let i = 0; i < n - 1; i++) {
            // If the current element is greater than the next one,
            // they are in the wrong order and need to be swapped
            if (arr[i] > arr[i + 1]) {
                // SWAP: Use a temporary variable to exchange values
                // Step 1: Store arr[i] in temp
                // Step 2: Move arr[i+1] into arr[i]
                // Step 3: Move temp into arr[i+1]
                let temp = arr[i];
                arr[i] = arr[i + 1];
                arr[i + 1] = temp;

                // Mark that a swap occurred — array may not be sorted yet
                swapped = true;
            }
        }
    } while (swapped);  // If no swaps occurred, array is fully sorted

    // Return the sorted array (note: it was sorted in-place)
    return arr;
}

/**
 * INSERTION SORT ALGORITHM
 * -----------------------------------------------
 * Sorts an array by building a sorted portion one element at
 * a time. It takes each element from the unsorted portion and
 * inserts it into its correct position in the sorted portion.
 *
 * How it works:
 *   1. Consider the first element as already sorted
 *   2. Take the next element (key) from the unsorted portion
 *   3. Compare key with each element in the sorted portion (right to left)
 *   4. Shift elements greater than key one position to the right
 *   5. Insert key into its correct position
 *   6. Repeat for all remaining unsorted elements
 *
 * Analogy: Like sorting playing cards in your hand — you pick up
 * each card and insert it into the correct position among the
 * cards you've already sorted.
 *
 * Time Complexity:  O(n²) — nested loops in worst case
 * Space Complexity: O(1) — sorts in-place, uses only key and j
 * Best Case:  O(n) — array is already sorted (inner loop never runs)
 * Worst Case: O(n²) — array is sorted in reverse order
 * Stability:  STABLE — equal elements maintain their relative order
 *
 * @param {Array} arr - The array to sort (modified in-place)
 * @return {Array}    - The sorted array (same reference)
 */
function insertionSort(arr) {
    // Start from index 1 (index 0 is considered already sorted)
    for (let i = 1; i < arr.length; i++) {
        // Store the current element as the "key" to be inserted
        let key = arr[i];

        // Start comparing with the element just before the key
        let j = i - 1;

        /**
         * SHIFT PHASE:
         * Move elements of the sorted portion (arr[0..i-1]) that are
         * GREATER than the key one position to the right.
         * This creates a gap for the key to be inserted.
         *
         * The while loop continues as long as:
         *   1. j >= 0 (we haven't gone past the beginning)
         *   2. arr[j] > key (the current sorted element is larger than key)
         */
        while (j >= 0 && arr[j] > key) {
            arr[j + 1] = arr[j];  // Shift element one position right
            j = j - 1;             // Move to the next element left
        }

        // INSERT: Place the key in its correct sorted position.
        // j+1 is the correct position because the while loop
        // stopped when arr[j] <= key (or j went below 0).
        arr[j + 1] = key;
    }

    // Return the sorted array (sorted in-place)
    return arr;
}

/**
 * QUICK SORT ALGORITHM
 * -----------------------------------------------
 * Sorts an array using a divide-and-conquer strategy.
 * It selects a "pivot" element and partitions the array into
 * two sub-arrays: elements less than pivot (left) and elements
 * greater than or equal to pivot (right). Then recursively
 * sorts both sub-arrays.
 *
 * How it works:
 *   1. Base case: arrays of 0 or 1 elements are already sorted
 *   2. Choose the last element as the pivot
 *   3. Partition: elements < pivot go to left[], others go to right[]
 *   4. Recursively sort left[] and right[]
 *   5. Concatenate: sorted left + pivot + sorted right
 *
 * Time Complexity:  O(n log n) average, O(n²) worst case
 * Space Complexity: O(n) — creates new arrays for left and right
 * Best Case:  O(n log n) — pivot always divides array in half
 * Worst Case: O(n²) — pivot is always min or max (already sorted)
 * Stability:  UNSTABLE — equal elements may change relative order
 *
 * @param {Array} arr - The array to sort
 * @return {Array}    - A NEW sorted array
 */
function quickSort(arr) {
    // BASE CASE: An array with 0 or 1 elements is already sorted
    if (arr.length <= 1) {
        return arr;
    }

    // Choose the last element as the pivot
    const pivot = arr[arr.length - 1];

    // Create two partition arrays
    const left = [];    // Elements LESS than pivot
    const right = [];   // Elements GREATER than or equal to pivot

    // Partition: distribute elements into left and right arrays
    // Note: we iterate up to length - 1 to exclude the pivot itself
    for (let i = 0; i < arr.length - 1; i++) {
        if (arr[i] < pivot) {
            left.push(arr[i]);    // Smaller elements go left
        } else {
            right.push(arr[i]);   // Larger/equal elements go right
        }
    }

    // RECURSIVE STEP: Sort both partitions and combine with pivot
    // Spread operator (...) concatenates the arrays:
    // [sorted left elements] + [pivot] + [sorted right elements]
    return [...quickSort(left), pivot, ...quickSort(right)];
}

/**
 * MERGE SORT ALGORITHM
 * -----------------------------------------------
 * Sorts an array using a divide-and-conquer strategy.
 * It divides the array into halves, recursively sorts each
 * half, and then merges the sorted halves back together.
 *
 * How it works:
 *   1. Base case: arrays of 0 or 1 elements are already sorted
 *   2. Find the middle index and split into left and right halves
 *   3. Recursively sort both halves
 *   4. Merge the two sorted halves into a single sorted array
 *
 * Time Complexity:  O(n log n) — always, regardless of input
 * Space Complexity: O(n) — requires additional arrays for merging
 * Best Case:  O(n log n) — same as worst case
 * Worst Case: O(n log n) — consistent performance
 * Stability:  STABLE — equal elements maintain their relative order
 *
 * @param {Array} arr - The array to sort
 * @return {Array}    - A NEW sorted array
 */
function mergeSort(arr) {
    // BASE CASE: An array with 0 or 1 elements is already sorted
    if (arr.length <= 1) {
        return arr;
    }

    // DIVIDE: Find the middle and split into two halves
    const mid = Math.floor(arr.length / 2);
    const left = arr.slice(0, mid);   // Left half: elements 0 to mid-1
    const right = arr.slice(mid);      // Right half: elements mid to end

    // CONQUER: Recursively sort both halves, then merge them
    return merge(mergeSort(left), mergeSort(right));
}

/**
 * MERGE HELPER FUNCTION (used by Merge Sort)
 * -----------------------------------------------
 * Merges two SORTED arrays into a single sorted array.
 * Uses a two-pointer technique to compare elements from
 * both arrays and build the result in sorted order.
 *
 * Time Complexity:  O(n) — visits each element exactly once
 * Space Complexity: O(n) — creates a new result array
 *
 * @param {Array} left  - First sorted array
 * @param {Array} right - Second sorted array
 * @return {Array}      - Merged sorted array
 */
function merge(left, right) {
    let result = [];      // The merged output array
    let leftIndex = 0;    // Pointer for the left array
    let rightIndex = 0;   // Pointer for the right array

    // Compare elements from both arrays and add the smaller one
    // Continue until one array is fully processed
    while (leftIndex < left.length && rightIndex < right.length) {
        if (left[leftIndex] < right[rightIndex]) {
            // Left element is smaller — add it to result
            result.push(left[leftIndex]);
            leftIndex++;   // Advance the left pointer
        } else {
            // Right element is smaller or equal — add it to result
            result.push(right[rightIndex]);
            rightIndex++;  // Advance the right pointer
        }
    }

    // APPEND REMAINING ELEMENTS
    // One array may still have elements left. Since both arrays
    // are already sorted, we can simply concatenate the remainder.
    return result.concat(left.slice(leftIndex)).concat(right.slice(rightIndex));
}


// =============================================
//  MODULE EXPORT (Node.js compatibility)
// =============================================
// If running in a Node.js environment, export the functions.
// In the browser, functions are automatically available globally
// when loaded via a <script> tag.
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
