import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class EnseignantsScreen extends StatefulWidget {
  const EnseignantsScreen({super.key});

  @override
  State<EnseignantsScreen> createState() => _EnseignantsScreenState();
}

class _EnseignantsScreenState extends State<EnseignantsScreen> {
  late Future<List<dynamic>> _futureEnseignants;
  final TextEditingController _searchCtrl = TextEditingController();
  String _searchQuery = '';

  @override
  void initState() {
    super.initState();
    _futureEnseignants = ApiService.getEnseignants();
  }

  void _refresh() {
    setState(() {
      _futureEnseignants = ApiService.getEnseignants();
    });
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    super.dispose();
  }

  void _showAddDialog() {
    final nomCtrl = TextEditingController();
    final prenomCtrl = TextEditingController();
    final emailCtrl = TextEditingController();
    final passwordCtrl = TextEditingController();
    final specCtrl = TextEditingController();

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Ajouter un enseignant'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: nomCtrl,
                decoration: const InputDecoration(labelText: 'Nom'),
              ),
              TextField(
                controller: prenomCtrl,
                decoration: const InputDecoration(labelText: 'Prénom'),
              ),
              TextField(
                controller: emailCtrl,
                decoration: const InputDecoration(labelText: 'Email'),
              ),
              TextField(
                controller: passwordCtrl,
                obscureText: true,
                decoration: const InputDecoration(labelText: 'Mot de passe'),
              ),
              TextField(
                controller: specCtrl,
                decoration: const InputDecoration(labelText: 'Spécialité'),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Annuler'),
          ),
          FilledButton(
            onPressed: () async {
              if (nomCtrl.text.isEmpty ||
                  prenomCtrl.text.isEmpty ||
                  emailCtrl.text.isEmpty ||
                  passwordCtrl.text.isEmpty) {
                return;
              }
              await ApiService.addEnseignant({
                'nom': nomCtrl.text,
                'prenom': prenomCtrl.text,
                'email': emailCtrl.text,
                'password': passwordCtrl.text,
                'specialite': specCtrl.text,
              });
              if (ctx.mounted) Navigator.pop(ctx);
              _refresh();
            },
            child: const Text('Ajouter'),
          ),
        ],
      ),
    );
  }

  void _showEditDialog(Map<String, dynamic> enseignant) {
    final nomCtrl = TextEditingController(text: enseignant['nom']);
    final prenomCtrl = TextEditingController(text: enseignant['prenom']);
    final emailCtrl = TextEditingController(text: enseignant['email']);
    final specCtrl = TextEditingController(text: enseignant['specialite']);

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Modifier l\'enseignant'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: nomCtrl,
                decoration: const InputDecoration(labelText: 'Nom'),
              ),
              TextField(
                controller: prenomCtrl,
                decoration: const InputDecoration(labelText: 'Prénom'),
              ),
              TextField(
                controller: emailCtrl,
                decoration: const InputDecoration(labelText: 'Email'),
              ),
              TextField(
                controller: specCtrl,
                decoration: const InputDecoration(labelText: 'Spécialité'),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Annuler'),
          ),
          FilledButton(
            onPressed: () async {
              await ApiService.updateEnseignant({
                'id': int.parse(enseignant['id'].toString()),
                'nom': nomCtrl.text,
                'prenom': prenomCtrl.text,
                'email': emailCtrl.text,
                'specialite': specCtrl.text,
              });
              if (ctx.mounted) Navigator.pop(ctx);
              _refresh();
            },
            child: const Text('Enregistrer'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: FutureBuilder<List<dynamic>>(
        future: _futureEnseignants,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snapshot.hasError) {
            return Center(child: Text('Erreur: ${snapshot.error}'));
          }

          final enseignants = snapshot.data ?? [];
          final filteredEnseignants = enseignants.where((e) {
            if (_searchQuery.trim().isEmpty) return true;
            final q = _searchQuery.toLowerCase();
            final text =
                '${e['nom'] ?? ''} ${e['prenom'] ?? ''} ${e['email'] ?? ''} ${e['specialite'] ?? ''}'
                    .toLowerCase();
            return text.contains(q);
          }).toList();

          if (enseignants.isEmpty) {
            return const Center(child: Text('Aucun enseignant'));
          }

          return Column(
            children: [
              Padding(
                padding: const EdgeInsets.fromLTRB(12, 12, 12, 4),
                child: TextField(
                  controller: _searchCtrl,
                  onChanged: (value) {
                    setState(() {
                      _searchQuery = value;
                    });
                  },
                  decoration: InputDecoration(
                    hintText: 'Rechercher un enseignant...',
                    prefixIcon: const Icon(Icons.search),
                    suffixIcon: _searchQuery.isEmpty
                        ? null
                        : IconButton(
                            icon: const Icon(Icons.clear),
                            onPressed: () {
                              _searchCtrl.clear();
                              setState(() {
                                _searchQuery = '';
                              });
                            },
                          ),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ),
              Expanded(
                child: filteredEnseignants.isEmpty
                    ? const Center(child: Text('Aucun enseignant trouvé'))
                    : ListView.builder(
                        padding: const EdgeInsets.all(8),
                        itemCount: filteredEnseignants.length,
                        itemBuilder: (context, index) {
                          final e = filteredEnseignants[index];
                          return Card(
                            child: ListTile(
                              leading: CircleAvatar(
                                child: Text(e['nom'][0].toUpperCase()),
                              ),
                              title: Text('${e['nom']} ${e['prenom']}'),
                              subtitle: Text(
                                e['specialite'] ?? 'Pas de spécialité',
                              ),
                              trailing: IconButton(
                                icon: const Icon(
                                  Icons.delete_outline,
                                  color: Colors.red,
                                ),
                                onPressed: () async {
                                  final confirm = await showDialog<bool>(
                                    context: context,
                                    builder: (ctx) => AlertDialog(
                                      title: const Text('Confirmer'),
                                      content: const Text(
                                        'Supprimer cet enseignant ?',
                                      ),
                                      actions: [
                                        TextButton(
                                          onPressed: () =>
                                              Navigator.pop(ctx, false),
                                          child: const Text('Non'),
                                        ),
                                        FilledButton(
                                          onPressed: () =>
                                              Navigator.pop(ctx, true),
                                          child: const Text('Oui'),
                                        ),
                                      ],
                                    ),
                                  );
                                  if (confirm == true) {
                                    await ApiService.deleteEnseignant(
                                      int.parse(e['id'].toString()),
                                    );
                                    _refresh();
                                  }
                                },
                              ),
                              onTap: () => _showEditDialog(e),
                            ),
                          );
                        },
                      ),
              ),
            ],
          );
        },
      ),
      floatingActionButton: FloatingActionButton(
        onPressed: _showAddDialog,
        child: const Icon(Icons.add),
      ),
    );
  }
}
