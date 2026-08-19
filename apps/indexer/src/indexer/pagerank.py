from typing import Dict, List, Set

class PageRankCalculator:
    """Iterative Power-Iteration PageRank algorithm for link graph analysis."""

    def __init__(self, damping_factor: float = 0.85, max_iterations: int = 50, tolerance: float = 1e-6):
        self.damping_factor = damping_factor
        self.max_iterations = max_iterations
        self.tolerance = tolerance

    def calculate(self, graph: Dict[str, List[str]]) -> Dict[str, float]:
        """
        Calculate PageRank for a directed graph where graph[node] = [outgoing_links].
        """
        nodes: Set[str] = set(graph.keys())
        for targets in graph.values():
            nodes.update(targets)

        num_nodes = len(nodes)
        if num_nodes == 0:
            return {}

        # Initialize ranks equally
        ranks = {node: 1.0 / num_nodes for node in nodes}
        
        # Precompute incoming links
        incoming: Dict[str, List[str]] = {node: [] for node in nodes}
        out_degrees: Dict[str, int] = {node: 0 for node in nodes}

        for source, targets in graph.items():
            valid_targets = [t for t in targets if t in nodes]
            out_degrees[source] = len(valid_targets)
            for target in valid_targets:
                incoming[target].append(source)

        for _ in range(self.max_iterations):
            new_ranks: Dict[str, float] = {}
            dangling_sum = sum(ranks[node] for node in nodes if out_degrees[node] == 0)

            for node in nodes:
                rank_sum = sum(ranks[in_neighbor] / out_degrees[in_neighbor] for in_neighbor in incoming[node])
                rank_sum += dangling_sum / num_nodes
                new_ranks[node] = (1 - self.damping_factor) / num_nodes + self.damping_factor * rank_sum

            # Check convergence
            diff = sum(abs(new_ranks[n] - ranks[n]) for n in nodes)
            ranks = new_ranks
            if diff < self.tolerance:
                break

        return ranks
