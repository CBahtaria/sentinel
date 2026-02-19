/**
 * SENTINEL v4.1 - Advanced Concurrency Detection Algorithms
 * Production-ready algorithms for deadlock prevention and race detection
 * @module sentinel-concurrency
 */

// =========================================
// Core Algorithm Classes
// =========================================

class ConcurrencyDetector {
    /**
     * Floyd-Warshall Algorithm - O(n³) Reachability Analysis
     * Computes transitive closure of a graph to detect resource dependencies
     * @param {number[][]} graph - Adjacency matrix (0/1 or weighted)
     * @returns {Object} Distance matrix and reachability info
     */
    static floydWarshall(graph) {
        const n = graph.length;
        
        // Initialize distance matrix
        const dist = Array(n).fill().map(() => Array(n).fill(Infinity));
        const reachability = Array(n).fill().map(() => Array(n).fill(false));
        
        for (let i = 0; i < n; i++) {
            dist[i][i] = 0;
            reachability[i][i] = true;
            for (let j = 0; j < n; j++) {
                if (graph[i][j]) {
                    dist[i][j] = graph[i][j];
                    reachability[i][j] = true;
                }
            }
        }
        
        // Floyd-Warshall core algorithm
        for (let k = 0; k < n; k++) {
            for (let i = 0; i < n; i++) {
                for (let j = 0; j < n; j++) {
                    if (dist[i][k] + dist[k][j] < dist[i][j]) {
                        dist[i][j] = dist[i][k] + dist[k][j];
                        reachability[i][j] = reachability[i][j] || (reachability[i][k] && reachability[k][j]);
                    }
                }
            }
        }
        
        return {
            distances: dist,
            reachability: reachability,
            hasCycle: this._detectCycle(reachability)
        };
    }
    
    /**
     * Banker's Algorithm - Deadlock Prevention O(n² * m)
     * Determines if system is in safe state and finds safe execution sequence
     * @param {Object[]} processes - Array of process objects with max and allocation
     * @param {number[]} resources - Available resources
     * @returns {Object} Safety analysis results
     */
    static bankersAlgorithm(processes, resources) {
        const n = processes.length;
        const m = resources.length;
        
        // Input validation
        if (!this._validateBankersInput(processes, resources)) {
            throw new Error('Invalid input for Banker\'s Algorithm');
        }
        
        // Initialize data structures
        const available = [...resources];
        const allocation = processes.map(p => p.allocation || Array(m).fill(0));
        const max = processes.map(p => p.max || Array(m).fill(0));
        const need = max.map((row, i) => 
            row.map((val, j) => Math.max(0, val - allocation[i][j]))
        );
        
        const work = [...available];
        const finish = Array(n).fill(false);
        const safeSequence = [];
        const executionSteps = [];
        
        let found;
        do {
            found = false;
            for (let i = 0; i < n; i++) {
                if (!finish[i] && this._canAllocate(need[i], work)) {
                    // Allocate resources to process i
                    for (let j = 0; j < m; j++) {
                        work[j] += allocation[i][j];
                    }
                    
                    safeSequence.push(i);
                    finish[i] = true;
                    found = true;
                    
                    executionSteps.push({
                        process: i,
                        allocated: [...allocation[i]],
                        available: [...work],
                        need: [...need[i]]
                    });
                    
                    break; // Restart search from beginning
                }
            }
        } while (found);
        
        const isSafe = finish.every(f => f);
        
        return {
            safe: isSafe,
            sequence: isSafe ? safeSequence : [],
            executionSteps: executionSteps,
            deadlockDetected: !isSafe,
            analysis: {
                totalProcesses: n,
                completedProcesses: finish.filter(f => f).length,
                blockedProcesses: finish.filter(f => !f).length,
                finalAvailable: work
            }
        };
    }
    
    /**
     * Vector Clocks - Happens-Before Analysis O(n log n)
     * Tracks causal relationships between events in distributed systems
     * @param {Object[]} events - Array of events with process, type, and optional message
     * @returns {Object[]} Events with vector clock timestamps
     */
    static vectorClocks(events) {
        const processes = [...new Set(events.map(e => e.process))].sort((a, b) => a - b);
        
        // Initialize clocks for all processes
        const clocks = new Map();
        processes.forEach(p => {
            const clock = {};
            processes.forEach(q => clock[q] = 0);
            clocks.set(p, clock);
        });
        
        const results = [];
        
        events.forEach((event, index) => {
            const { process, type, variable = null, message = null } = event;
            
            if (!clocks.has(process)) {
                throw new Error(`Unknown process: ${process}`);
            }
            
            const clock = clocks.get(process);
            
            // Handle different event types
            switch (type) {
                case 'internal':
                    clock[process]++;
                    break;
                    
                case 'send':
                    clock[process]++;
                    event.message = {
                        data: message,
                        clock: {...clock}
                    };
                    break;
                    
                case 'receive':
                    if (message && message.clock) {
                        // Merge clocks from sender
                        processes.forEach(p => {
                            clock[p] = Math.max(clock[p] || 0, message.clock[p] || 0);
                        });
                    }
                    clock[process]++; // Receive event
                    break;
                    
                case 'read':
                case 'write':
                    clock[process]++;
                    break;
                    
                default:
                    clock[process]++; // Default behavior
            }
            
            results.push({
                id: index,
                process,
                type,
                variable,
                clock: {...clock},
                timestamp: index,
                causalHistory: this._computeCausalHistory(results, process, clock)
            });
        });
        
        return {
            events: results,
            statistics: this._computeClockStatistics(results, processes)
        };
    }
    
    /**
     * Tarjan's SCC Algorithm - Livelock Detection O(V+E)
     * Finds strongly connected components to detect cycles and livelocks
     * @param {Object} graph - Adjacency list representation
     * @returns {Object} SCC analysis results
     */
    static tarjanSCC(graph) {
        let index = 0;
        const stack = [];
        const indices = new Map();
        const lowlinks = new Map();
        const onStack = new Set();
        const sccs = [];
        const nodeOrder = [];
        
        const strongconnect = (node) => {
            indices.set(node, index);
            lowlinks.set(node, index);
            index++;
            stack.push(node);
            onStack.add(node);
            nodeOrder.push(node);
            
            const neighbors = graph[node] || [];
            neighbors.forEach(neighbor => {
                if (!indices.has(neighbor)) {
                    strongconnect(neighbor);
                    lowlinks.set(node, Math.min(lowlinks.get(node), lowlinks.get(neighbor)));
                } else if (onStack.has(neighbor)) {
                    lowlinks.set(node, Math.min(lowlinks.get(node), indices.get(neighbor)));
                }
            });
            
            if (lowlinks.get(node) === indices.get(node)) {
                const scc = [];
                let w;
                do {
                    w = stack.pop();
                    onStack.delete(w);
                    scc.push(w);
                } while (w !== node);
                
                sccs.push({
                    nodes: scc,
                    size: scc.length,
                    isCycle: scc.length > 1 || (scc.length === 1 && graph[scc[0]]?.includes(scc[0]))
                });
            }
        };
        
        // Process all nodes
        Object.keys(graph).forEach(node => {
            const numNode = parseInt(node);
            if (!indices.has(numNode)) {
                strongconnect(numNode);
            }
        });
        
        // Analyze SCCs for deadlock potential
        const cycles = sccs.filter(scc => scc.isCycle);
        const potentialDeadlocks = cycles.filter(scc => 
            scc.nodes.some(node => 
                graph[node] && graph[node].length > 0
            )
        );
        
        return {
            stronglyConnectedComponents: sccs,
            totalComponents: sccs.length,
            cycles: cycles,
            cycleCount: cycles.length,
            potentialDeadlocks: potentialDeadlocks,
            hasLivelock: cycles.length > 0,
            nodeOrder: nodeOrder,
            analysis: this._analyzeSCCs(sccs, graph)
        };
    }
    
    // =========================================
    // Private Helper Methods
    // =========================================
    
    static _validateBankersInput(processes, resources) {
        if (!Array.isArray(processes) || !Array.isArray(resources)) return false;
        if (processes.length === 0 || resources.length === 0) return false;
        
        return processes.every(p => 
            Array.isArray(p.allocation) && 
            Array.isArray(p.max) &&
            p.allocation.length === resources.length &&
            p.max.length === resources.length
        );
    }
    
    static _canAllocate(need, work) {
        return need.every((value, index) => value <= work[index]);
    }
    
    static _detectCycle(reachability) {
        const n = reachability.length;
        for (let i = 0; i < n; i++) {
            if (reachability[i][i] && n > 1) return true;
        }
        return false;
    }
    
    static _computeCausalHistory(results, currentProcess, currentClock) {
        return results.filter(event => {
            if (event.process === currentProcess) return false;
            const eventClock = event.clock;
            return Object.keys(currentClock).every(p => 
                currentClock[p] >= (eventClock[p] || 0)
            );
        }).map(e => e.id);
    }
    
    static _computeClockStatistics(results, processes) {
        const stats = {
            maxClock: {},
            totalEvents: results.length,
            eventsPerProcess: {}
        };
        
        processes.forEach(p => stats.eventsPerProcess[p] = 0);
        
        results.forEach(event => {
            stats.eventsPerProcess[event.process]++;
            processes.forEach(p => {
                stats.maxClock[p] = Math.max(
                    stats.maxClock[p] || 0, 
                    event.clock[p] || 0
                );
            });
        });
        
        return stats;
    }
    
    static _analyzeSCCs(sccs, graph) {
        return {
            averageComponentSize: sccs.reduce((sum, scc) => sum + scc.size, 0) / sccs.length,
            largestComponent: Math.max(...sccs.map(scc => scc.size)),
            smallestComponent: Math.min(...sccs.map(scc => scc.size)),
            isolatedNodes: sccs.filter(scc => scc.size === 1 && !scc.isCycle).length
        };
    }
}

// =========================================
// Test Data Generators
// =========================================

class TestDataGenerator {
    /**
     * Generate random graph for testing
     * @param {number} size - Number of nodes
     * @param {number} density - Edge probability (0-1)
     * @param {boolean} weighted - Generate weighted edges
     * @returns {Object} Graph in multiple formats
     */
    static generateRandomGraph(size = 10, density = 0.3, weighted = false) {
        const adjacencyMatrix = [];
        const adjacencyList = {};
        
        for (let i = 0; i < size; i++) {
            adjacencyMatrix[i] = [];
            adjacencyList[i] = [];
            
            for (let j = 0; j < size; j++) {
                if (i !== j && Math.random() < density) {
                    const value = weighted ? Math.floor(Math.random() * 10) + 1 : 1;
                    adjacencyMatrix[i][j] = value;
                    adjacencyList[i].push(j);
                } else {
                    adjacencyMatrix[i][j] = 0;
                }
            }
        }
        
        return {
            matrix: adjacencyMatrix,
            list: adjacencyList,
            size: size,
            edgeCount: adjacencyList[i]?.length || 0,
            density: density
        };
    }
    
    /**
     * Generate random processes for Banker's Algorithm
     * @param {number} count - Number of processes
     * @param {number} resourceTypes - Number of resource types
     * @param {Object} constraints - Optional constraints
     * @returns {Object} Processes and resources
     */
    static generateRandomProcesses(count = 5, resourceTypes = 3, constraints = {}) {
        const {
            maxResourcePerType = 10,
            maxAllocationPerType = 5,
            totalResources = null
        } = constraints;
        
        const processes = [];
        let totalAllocated = Array(resourceTypes).fill(0);
        
        for (let i = 0; i < count; i++) {
            const max = [];
            const allocation = [];
            
            for (let j = 0; j < resourceTypes; j++) {
                max.push(Math.floor(Math.random() * maxResourcePerType) + 1);
                const alloc = Math.min(
                    Math.floor(Math.random() * maxAllocationPerType),
                    max[j]
                );
                allocation.push(alloc);
                totalAllocated[j] += alloc;
            }
            
            processes.push({ max, allocation });
        }
        
        // Generate available resources
        let available;
        if (totalResources) {
            available = totalResources.map((total, j) => 
                Math.max(0, total - totalAllocated[j])
            );
        } else {
            available = Array(resourceTypes).fill()
                .map(() => Math.floor(Math.random() * 5) + 1);
        }
        
        return { processes, available };
    }
    
    /**
     * Generate random events for vector clock testing
     * @param {number} numEvents - Number of events
     * @param {number} numProcesses - Number of processes
     * @returns {Object[]} Random events
     */
    static generateRandomEvents(numEvents = 20, numProcesses = 3) {
        const events = [];
        const eventTypes = ['internal', 'send', 'receive', 'read', 'write'];
        
        for (let i = 0; i < numEvents; i++) {
            events.push({
                process: Math.floor(Math.random() * numProcesses),
                type: eventTypes[Math.floor(Math.random() * eventTypes.length)],
                variable: `x${Math.floor(Math.random() * 5)}`,
                timestamp: i
            });
        }
        
        return events;
    }
}

// =========================================
// Utility Functions
// =========================================

const ConcurrencyUtils = {
    /**
     * Pretty print analysis results
     * @param {Object} results - Analysis results
     * @param {string} algorithm - Algorithm name
     */
    printResults(results, algorithm) {
        console.log(`\n📊 ${algorithm} Results:`);
        console.log('='.repeat(50));
        console.log(JSON.stringify(results, null, 2));
    },
    
    /**
     * Compare results from multiple algorithms
     * @param {Object[]} results - Array of algorithm results
     */
    compareAlgorithms(results) {
        return results.map(r => ({
            algorithm: r.algorithm,
            timeComplexity: r.complexity,
            detected: r.detected,
            confidence: r.confidence || 'N/A'
        }));
    }
};

// =========================================
// Export module (for Node.js) or attach to window (for browser)
// =========================================

if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        ConcurrencyDetector,
        TestDataGenerator,
        ConcurrencyUtils
    };
} else if (typeof window !== 'undefined') {
    window.SENTINEL = {
        ConcurrencyDetector,
        TestDataGenerator,
        ConcurrencyUtils
    };
}

// =========================================
// Self-test demonstration
// =========================================

function runSentinelDemo() {
    console.log('🚀 SENTINEL v4.1 - Concurrency Detection Demo');
    console.log('=============================================\n');
    
    // Test Floyd-Warshall
    const graph = TestDataGenerator.generateRandomGraph(5, 0.4);
    const fwResult = ConcurrencyDetector.floydWarshall(graph.matrix);
    console.log('✓ Floyd-Warshall: Cycle detected:', fwResult.hasCycle);
    
    // Test Banker's Algorithm
    const { processes, available } = TestDataGenerator.generateRandomProcesses(4, 3);
    const bankerResult = ConcurrencyDetector.bankersAlgorithm(processes, available);
    console.log('✓ Banker\'s Algorithm: Safe state:', bankerResult.safe);
    
    // Test Vector Clocks
    const events = TestDataGenerator.generateRandomEvents(10, 3);
    const vcResult = ConcurrencyDetector.vectorClocks(events);
    console.log('✓ Vector Clocks: Events processed:', vcResult.events.length);
    
    // Test Tarjan's SCC
    const sccResult = ConcurrencyDetector.tarjanSCC(graph.list);
    console.log('✓ Tarjan\'s SCC: Components found:', sccResult.totalComponents);
    
    console.log('\n✨ Demo complete!');
}

// Auto-run demo if in browser or directly executed
if (typeof require !== 'undefined' && require.main === module) {
    runSentinelDemo();
}